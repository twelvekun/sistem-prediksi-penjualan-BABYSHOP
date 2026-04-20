<?php
// Koneksi ke database
$conn = mysqli_connect("localhost", "root", "", "skrip2");

if (!$conn) {
    die("Koneksi gagal: " . mysqli_connect_error());
}

// Parameter input
$idproduk = 'se001';
$tahun = '2025';
$bulan = '5';

// Query data historis
$query = "SELECT SUM(JUMLAHJUAL) AS total_jual, BULAN, TAHUN 
          FROM penjualan 
          WHERE IDPRODUK = '$idproduk'
          AND (TAHUN < '$tahun' OR (TAHUN = '$tahun' AND BULAN <= '$bulan'))
          GROUP BY BULAN, TAHUN
          ORDER BY TAHUN ASC, BULAN ASC
          LIMIT 15"; // Ambil lebih banyak data untuk validasi

$result = mysqli_query($conn, $query);
if (!$result) {
    die("Error: " . mysqli_error($conn));
}

// Persiapan data
$sales_data = [];
$periods = [];
while ($row = mysqli_fetch_assoc($result)) {
    $sales_data[] = $row['total_jual'];
    $periods[] = $row['BULAN'] . '/' . $row['TAHUN'];
}

if (count($sales_data) < 10) {
    die("Data historis kurang dari 10 periode. Tidak bisa melakukan validasi model.");
}

// Bagi data menjadi training (80%) dan testing (20%)
$split_index = floor(count($sales_data) * 0.8);
$train_data = array_slice($sales_data, 0, $split_index);
$test_data = array_slice($sales_data, $split_index);

// Fungsi Single Exponential Smoothing yang diperbaiki
function optimizedSES($data, $alpha, $forecast_periods = 1) {
    // Inisialisasi dengan rata-rata 3 periode pertama
    $initial = count($data) > 3 ? array_sum(array_slice($data, 0, 3)) / 3 : $data[0];
    $smoothed = [];
    $smoothed[0] = $initial;
    
    // Proses smoothing
    for ($i = 1; $i < count($data); $i++) {
        $smoothed[$i] = $alpha * $data[$i] + (1 - $alpha) * $smoothed[$i-1];
    }
    
    // Hitung error metrics
    $errors = [];
    for ($i = 1; $i < count($data); $i++) {
        $errors[] = $data[$i] - $smoothed[$i-1];
    }
    
    $absolute_errors = array_map('abs', $errors);
    $percentage_errors = [];
    
    for ($i = 0; $i < count($errors); $i++) {
        $actual = $data[$i+1];
        if ($actual != 0) {
            $percentage_errors[] = abs($errors[$i]) / $actual * 100;
        }
    }
    
    $mad = count($absolute_errors) > 0 ? array_sum($absolute_errors)/count($absolute_errors) : 0;
    $mse = count($errors) > 0 ? array_sum(array_map(function($e) { return $e*$e; }, $errors))/count($errors) : 0;
    $mape = count($percentage_errors) > 0 ? array_sum($percentage_errors)/count($percentage_errors) : 0;
    
    // Forecast
    $forecast = array_fill(0, $forecast_periods, end($smoothed));
    
    return [
        'forecast' => $forecast,
        'smoothed' => $smoothed,
        'mad' => $mad,
        'mse' => $mse,
        'mape' => $mape,
        'accuracy' => max(0, 100 - $mape)
    ];
}

// Fungsi Double Exponential Smoothing
function doubleES($data, $alpha, $beta, $forecast_periods = 1) {
    $level = [];
    $trend = [];
    $forecast = [];
    
    // Inisialisasi
    $level[0] = $data[0];
    $trend[0] = $data[1] - $data[0];
    
    // Smoothing
    for ($i = 1; $i < count($data); $i++) {
        $level[$i] = $alpha * $data[$i] + (1 - $alpha) * ($level[$i-1] + $trend[$i-1]);
        $trend[$i] = $beta * ($level[$i] - $level[$i-1]) + (1 - $beta) * $trend[$i-1];
    }
    
    // Hitung error metrics
    $errors = [];
    for ($i = 1; $i < count($data); $i++) {
        $errors[] = $data[$i] - ($level[$i-1] + $trend[$i-1]);
    }
    
    $absolute_errors = array_map('abs', $errors);
    $percentage_errors = [];
    
    for ($i = 0; $i < count($errors); $i++) {
        $actual = $data[$i+1];
        if ($actual != 0) {
            $percentage_errors[] = abs($errors[$i]) / $actual * 100;
        }
    }
    
    $mad = count($absolute_errors) > 0 ? array_sum($absolute_errors)/count($absolute_errors) : 0;
    $mse = count($errors) > 0 ? array_sum(array_map(function($e) { return $e*$e; }, $errors))/count($errors) : 0;
    $mape = count($percentage_errors) > 0 ? array_sum($percentage_errors)/count($percentage_errors) : 0;
    
    // Forecast
    $last_level = end($level);
    $last_trend = end($trend);
    for ($i = 0; $i < $forecast_periods; $i++) {
        $forecast[] = $last_level + ($last_trend * ($i+1));
    }
    
    return [
        'forecast' => $forecast,
        'level' => $level,
        'trend' => $trend,
        'mad' => $mad,
        'mse' => $mse,
        'mape' => $mape,
        'accuracy' => max(0, 100 - $mape)
    ];
}

// Optimasi parameter alpha untuk SES
$best_ses = null;
$best_alpha = 0.1;
$best_mape = PHP_FLOAT_MAX;

$alpha_values = [0.1, 0.2, 0.3, 0.4, 0.5, 0.6, 0.7];
foreach ($alpha_values as $alpha) {
    $result = optimizedSES($train_data, $alpha, count($test_data));
    $test_result = optimizedSES($test_data, $alpha, 1);
    
    // Gabungkan error training dan testing
    $combined_mape = ($result['mape'] + $test_result['mape']) / 2;
    
    if ($combined_mape < $best_mape) {
        $best_mape = $combined_mape;
        $best_alpha = $alpha;
        $best_ses = $result;
    }
}

// Optimasi parameter untuk DES
$best_des = null;
$best_des_params = ['alpha' => 0.1, 'beta' => 0.1];
$best_des_mape = PHP_FLOAT_MAX;

$beta_values = [0.1, 0.2, 0.3, 0.4];
foreach ($alpha_values as $alpha) {
    foreach ($beta_values as $beta) {
        $result = doubleES($train_data, $alpha, $beta, count($test_data));
        $test_result = doubleES($test_data, $alpha, $beta, 1);
        
        $combined_mape = ($result['mape'] + $test_result['mape']) / 2;
        
        if ($combined_mape < $best_des_mape) {
            $best_des_mape = $combined_mape;
            $best_des_params = ['alpha' => $alpha, 'beta' => $beta];
            $best_des = $result;
        }
    }
}

// Tentukan model terbaik
$use_des = false;
if ($best_des_mape < $best_mape * 0.9) { // DES lebih baik minimal 10%
    $use_des = true;
}

// Prediksi untuk periode berikutnya
$next_periods = 11 - count($sales_data);
if ($next_periods < 1) $next_periods = 1;

if ($use_des) {
    $final_forecast = doubleES($sales_data, $best_des_params['alpha'], $best_des_params['beta'], $next_periods);
    $model_used = "Double Exponential Smoothing";
    $params_used = "Alpha: {$best_des_params['alpha']}, Beta: {$best_des_params['beta']}";
} else {
    $final_forecast = optimizedSES($sales_data, $best_alpha, $next_periods);
    $model_used = "Single Exponential Smoothing";
    $params_used = "Alpha: $best_alpha";
}

// Generate periode prediksi
$last_period = end($periods);
list($last_month, $last_year) = explode('/', $last_period);
$last_month = (int)$last_month;
$last_year = (int)$last_year;

$forecast_periods = [];
for ($i = 0; $i < $next_periods; $i++) {
    $last_month++;
    if ($last_month > 12) {
        $last_month = 1;
        $last_year++;
    }
    $forecast_periods[] = $last_month . '/' . $last_year;
}

// Tampilkan hasil
echo "<h2>Hasil Analisis Model Terbaik</h2>";
echo "Model yang digunakan: <strong>$model_used</strong><br>";
echo "Parameter: $params_used<br>";
echo "MAPE pada data training: " . number_format($use_des ? $best_des_mape : $best_mape, 2) . "%<br>";

echo "<h3>Data Historis Penjualan</h3>";
echo "<table border='1' cellpadding='5'>";
echo "<tr><th>Periode</th><th>Bulan/Tahun</th><th>Penjualan Aktual</th>";
if (!$use_des) {
    echo "<th>Nilai Smoothed</th>";
} else {
    echo "<th>Level</th><th>Trend</th>";
}
echo "</tr>";

foreach ($sales_data as $index => $value) {
    echo "<tr>";
    echo "<td>" . ($index + 1) . "</td>";
    echo "<td>" . $periods[$index] . "</td>";
    echo "<td>" . $value . "</td>";
    
    if (!$use_des) {
        $smoothed = isset($final_forecast['smoothed'][$index]) ? number_format($final_forecast['smoothed'][$index], 2) : '-';
        echo "<td>$smoothed</td>";
    } else {
        $level = isset($final_forecast['level'][$index]) ? number_format($final_forecast['level'][$index], 2) : '-';
        $trend = isset($final_forecast['trend'][$index]) ? number_format($final_forecast['trend'][$index], 2) : '-';
        echo "<td>$level</td><td>$trend</td>";
    }
    
    echo "</tr>";
}
echo "</table>";

echo "<h3>Prediksi untuk Periode Berikutnya</h3>";
echo "<table border='1' cellpadding='5'>";
echo "<tr><th>Periode</th><th>Bulan/Tahun</th><th>Prediksi Penjualan</th></tr>";

foreach ($final_forecast['forecast'] as $index => $value) {
    echo "<tr>";
    echo "<td>" . (count($sales_data) + $index + 1) . "</td>";
    echo "<td>" . $forecast_periods[$index] . "</td>";
    echo "<td>" . number_format($value, 2) . "</td>";
    echo "</tr>";
}
echo "</table>";

// Rekomendasi
echo "<h3>Evaluasi Model</h3>";
echo "MAD: " . number_format($final_forecast['mad'], 2) . "<br>";
echo "MSE: " . number_format($final_forecast['mse'], 2) . "<br>";
echo "MAPE: " . number_format($final_forecast['mape'], 2) . "%<br>";
echo "Accuracy: " . number_format($final_forecast['accuracy'], 2) . "%<br>";

if ($final_forecast['mape'] > 30) {
    echo "<div style='background:#fff8f8; border-left:4px solid #f88; padding:10px; margin-top:15px;'>";
    echo "<strong>Rekomendasi:</strong> Model masih memiliki error yang tinggi. Pertimbangkan untuk:";
    echo "<ul>
            <li>Mengumpulkan lebih banyak data historis</li>
            <li>Memeriksa dan menghandle outlier</li>
            <li>Menggunakan metode yang lebih canggih seperti ARIMA atau Machine Learning</li>
            <li>Mempertimbangkan faktor musiman jika ada</li>
          </ul>";
    echo "</div>";
}

mysqli_close($conn);
?>