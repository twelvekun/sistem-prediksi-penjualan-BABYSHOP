<?php
// Koneksi ke database
$conn = mysqli_connect("localhost", "root", "", "skrip2");

if (!$conn) {
    die("Koneksi gagal: " . mysqli_connect_error());
}

// Mendapatkan parameter dari form atau input
$idproduk = 'se001'; // Produk yang dipilih
$tahun = '2025';
$bulan = '5';

// Query untuk mendapatkan data historis
$query_penjualan = "
    SELECT SUM(JUMLAHJUAL) AS total_jual, BULAN, TAHUN
    FROM penjualan
    WHERE IDPRODUK = '$idproduk'
    AND (TAHUN < '$tahun' OR (TAHUN = '$tahun' AND BULAN <= '$bulan'))
    GROUP BY BULAN, TAHUN
    ORDER BY TAHUN ASC, BULAN ASC
    LIMIT 10
";

// Menjalankan query
$result_penjualan = mysqli_query($conn, $query_penjualan);

if (!$result_penjualan) {
    die("Error: " . mysqli_error($conn));
}

// Mengumpulkan data penjualan
$sales_data = array();
$periods = array();
while ($row = mysqli_fetch_assoc($result_penjualan)) {
    $sales_data[] = $row['total_jual'];
    $periods[] = $row['BULAN'] . '/' . $row['TAHUN'];
}

if (empty($sales_data)) {
    die("Tidak ada data penjualan untuk produk ini.");
}

// Fungsi Single Exponential Smoothing untuk prediksi multi-periode
function forecastSES($data, $alpha = 0.1, $forecast_periods = 1) {
    $smoothed = array();
    $forecast = array();
    $errors = array();
    
    // Inisialisasi dengan rata-rata 3 periode pertama
    $initial = count($data) >= 3 ? array_sum(array_slice($data, 0, 3)) / 3 : (count($data) > 0 ? $data[0] : 0);
    $smoothed[0] = $initial;
    
    // Smoothing data historis
    for ($i = 1; $i < count($data); $i++) {
        $smoothed[$i] = $alpha * $data[$i] + (1 - $alpha) * $smoothed[$i-1];
        $errors[$i] = $data[$i] - $smoothed[$i-1];
    }
    
    // Membuat forecast untuk periode berikutnya
    $last_smoothed = end($smoothed);
    for ($i = 0; $i < $forecast_periods; $i++) {
        $forecast[] = $last_smoothed;
    }
    
    // Hitung error metrics
    $absolute_errors = array_map('abs', array_slice($errors, 1));
    $squared_errors = array_map(function($x) { return pow($x, 2); }, array_slice($errors, 1));
    $percentage_errors = array();
    
    for ($i = 1; $i < count($data); $i++) {
        if ($data[$i] != 0) {
            $percentage_errors[] = (abs($errors[$i]) / $data[$i]) * 100;
        } else {
            $percentage_errors[] = 0;
        }
    }
    
    $n = count($absolute_errors);
    $mad = $n > 0 ? array_sum($absolute_errors) / $n : 0;
    $mse = $n > 0 ? array_sum($squared_errors) / $n : 0;
    $mape = $n > 0 ? array_sum($percentage_errors) / $n : 0;
    $accuracy = 100 - $mape;
    
    return array(
        'smoothed_values' => $smoothed,
        'forecast' => $forecast,
        'mad' => $mad,
        'mse' => $mse,
        'mape' => $mape,
        'accuracy' => $accuracy
    );
}

// Nilai-nilai alpha yang akan dibandingkan
$alpha_values = [0.1, 0.3, 0.5, 0.7, 0.9];
$forecast_periods = 11 - count($sales_data); // Prediksi sampai bulan ke-11

// Menyimpan hasil semua prediksi
$all_results = array();

// Melakukan prediksi untuk setiap alpha
foreach ($alpha_values as $alpha) {
    $all_results[$alpha] = forecastSES($sales_data, $alpha, $forecast_periods);
}

// Menampilkan hasil
echo "<h2>Data Historis Penjualan</h2>";
echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
echo "<tr>
        <th style='padding: 8px; text-align: left; background-color: #f2f2f2;'>Periode</th>
        <th style='padding: 8px; text-align: left; background-color: #f2f2f2;'>Bulan/Tahun</th>
        <th style='padding: 8px; text-align: left; background-color: #f2f2f2;'>Penjualan Aktual</th>";

// Header untuk nilai smoothed tiap alpha
foreach ($alpha_values as $alpha) {
    echo "<th style='padding: 8px; text-align: left; background-color: #f2f2f2;'>Smoothed (α=$alpha)</th>";
}
echo "</tr>";

foreach ($sales_data as $index => $value) {
    echo "<tr>";
    echo "<td style='padding: 8px; border-bottom: 1px solid #ddd;'>" . ($index + 1) . "</td>";
    echo "<td style='padding: 8px; border-bottom: 1px solid #ddd;'>" . $periods[$index] . "</td>";
    echo "<td style='padding: 8px; border-bottom: 1px solid #ddd;'>" . $value . "</td>";
    
    // Nilai smoothed untuk tiap alpha
    foreach ($alpha_values as $alpha) {
        $smoothed_value = isset($all_results[$alpha]['smoothed_values'][$index]) ? 
                          number_format($all_results[$alpha]['smoothed_values'][$index], 2) : '-';
        echo "<td style='padding: 8px; border-bottom: 1px solid #ddd;'>" . $smoothed_value . "</td>";
    }
    
    echo "</tr>";
}
echo "</table>";

// Menampilkan hasil prediksi untuk tiap alpha
echo "<h2>Perbandingan Hasil Prediksi untuk Berbagai Nilai Alpha</h2>";

echo "<table border='1' style='border-collapse: collapse; width: 100%; margin-bottom: 20px;'>";
echo "<tr>
        <th style='padding: 8px; text-align: left; background-color: #f2f2f2;'>Alpha (α)</th>
        <th style='padding: 8px; text-align: left; background-color: #f2f2f2;'>Prediksi Bulan ke-11</th>
        <th style='padding: 8px; text-align: left; background-color: #f2f2f2;'>MAD</th>
        <th style='padding: 8px; text-align: left; background-color: #f2f2f2;'>MSE</th>
        <th style='padding: 8px; text-align: left; background-color: #f2f2f2;'>MAPE</th>
        <th style='padding: 8px; text-align: left; background-color: #f2f2f2;'>Accuracy</th>
      </tr>";

// Cari alpha dengan akurasi terbaik
$best_alpha = null;
$best_accuracy = 0;

foreach ($alpha_values as $alpha) {
    $result = $all_results[$alpha];
    $current_accuracy = $result['accuracy'];
    
    if ($current_accuracy > $best_accuracy) {
        $best_accuracy = $current_accuracy;
        $best_alpha = $alpha;
    }
    
    // Format warna untuk alpha terbaik
    $row_style = ($alpha == $best_alpha) ? "background-color: #e6ffe6;" : "";
    
    echo "<tr style='$row_style'>";
    echo "<td style='padding: 8px; border-bottom: 1px solid #ddd;'>" . $alpha . "</td>";
    echo "<td style='padding: 8px; border-bottom: 1px solid #ddd;'>" . number_format($result['forecast'][0], 2) . "</td>";
    echo "<td style='padding: 8px; border-bottom: 1px solid #ddd;'>" . number_format($result['mad'], 2) . "</td>";
    echo "<td style='padding: 8px; border-bottom: 1px solid #ddd;'>" . number_format($result['mse'], 2) . "</td>";
    echo "<td style='padding: 8px; border-bottom: 1px solid #ddd;'>" . number_format($result['mape'], 2) . "%</td>";
    echo "<td style='padding: 8px; border-bottom: 1px solid #ddd;'>" . number_format($result['accuracy'], 2) . "%</td>";
    echo "</tr>";
}
echo "</table>";

// Menampilkan detail prediksi untuk alpha terbaik
echo "<h2>Detail Prediksi dengan Alpha Terbaik (α = $best_alpha)</h2>";

$best_result = $all_results[$best_alpha];
echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
echo "<tr><th style='padding: 8px; text-align: left; background-color: #f2f2f2;'>Periode Prediksi</th>
          <th style='padding: 8px; text-align: left; background-color: #f2f2f2;'>Bulan/Tahun</th>
          <th style='padding: 8px; text-align: left; background-color: #f2f2f2;'>Hasil Prediksi</th></tr>";

$last_period = end($periods);
list($last_month, $last_year) = explode('/', $last_period);
$last_month = (int)$last_month;
$last_year = (int)$last_year;

for ($i = 0; $i < $forecast_periods; $i++) {
    $last_month++;
    if ($last_month > 12) {
        $last_month = 1;
        $last_year++;
    }
    
    echo "<tr>";
    echo "<td style='padding: 8px; border-bottom: 1px solid #ddd;'>" . (count($sales_data) + $i + 1) . "</td>";
    echo "<td style='padding: 8px; border-bottom: 1px solid #ddd;'>" . $last_month . "/" . $last_year . "</td>";
    echo "<td style='padding: 8px; border-bottom: 1px solid #ddd;'>" . number_format($best_result['forecast'][$i], 2) . "</td>";
    echo "</tr>";
}
echo "</table>";

// Analisis rekomendasi
echo "<h3>Analisis Hasil:</h3>";
echo "<ul>";
echo "<li>Alpha terbaik: <strong>$best_alpha</strong> dengan akurasi " . number_format($best_accuracy, 2) . "%</li>";

if ($best_alpha == max($alpha_values)) {
    echo "<li>Alpha tinggi (0.9) memberikan hasil terbaik, menunjukkan data mungkin memiliki perubahan yang cepat</li>";
} elseif ($best_alpha == min($alpha_values)) {
    echo "<li>Alpha rendah (0.1) memberikan hasil terbaik, menunjukkan data relatif stabil</li>";
} else {
    echo "<li>Alpha menengah ($best_alpha) memberikan hasil terbaik, menunjukkan pola data yang moderat</li>";
}

if ($best_accuracy < 70) {
    echo "<li style='color: red;'>Akurasi masih rendah, pertimbangkan untuk menggunakan metode lain seperti Double Exponential Smoothing atau ARIMA</li>";
} elseif ($best_accuracy < 85) {
    echo "<li style='color: orange;'>Akurasi cukup baik tetapi masih ada ruang untuk perbaikan</li>";
} else {
    echo "<li style='color: green;'>Akurasi sangat baik, model SES dengan alpha $best_alpha cocok untuk data ini</li>";
}
echo "</ul>";

// Menutup koneksi
mysqli_close($conn);
?>