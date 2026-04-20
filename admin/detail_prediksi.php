<?php
session_start();
if (!isset($_SESSION['log']) || $_SESSION['role'] !== 'admin') {
    echo "Akses ditolak.";
    exit();
}

require '../config.php';

// Definisi array nama bulan
$bulan_nama = [
    1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
    5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
    9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'
];

// Cek apakah ID prediksi tersedia
if (!isset($_GET['id'])) {
    header("Location: riwayat_prediksi.php");
    exit();
}

$id = $_GET['id'];

// Ambil data detail prediksi
$detail_query = "SELECT p.IDPRED, p.WAKTUPRED, p.HASILPRED, p.PREDIKSIBULANTAHUN, p.NILAI_PREDIKSI, 
                p.MAPE, p.ALPHA, pr.NAMAPRODUK, k.JENISPRODUK 
                FROM pred p
                JOIN produk pr ON p.HASILPRED = pr.IDPRODUK
                LEFT JOIN kategori k ON pr.IDKATEGORI = k.IDKATEGORI
                WHERE p.IDPRED = '$id'";
$detail_result = mysqli_query($conn, $detail_query);

if (!$detail_result || mysqli_num_rows($detail_result) == 0) {
    header("Location: riwayat_prediksi.php?msg=not-found");
    exit();
}

$prediksi = mysqli_fetch_assoc($detail_result);

// Parse periode prediksi
$periode_parts = explode('-', $prediksi['PREDIKSIBULANTAHUN']);
if (count($periode_parts) == 2) {
    $bulan_num = (int)$periode_parts[0];
    $tahun = $periode_parts[1];
    $periode_display = $bulan_nama[$bulan_num] . ' ' . $tahun;
} else {
    $periode_display = $prediksi['PREDIKSIBULANTAHUN'];
}

// Gabungkan kategori dan nama produk
$display_produk = !empty($prediksi['JENISPRODUK']) ? 
                $prediksi['JENISPRODUK'] . " " . $prediksi['NAMAPRODUK'] : 
                $prediksi['NAMAPRODUK'];

// Ambil data penjualan untuk produk ini (digunakan untuk tabel perhitungan dan chart)
$produk_id = $prediksi['HASILPRED'];
$query_sales = "SELECT BULAN, TAHUN, SUM(JUMLAHJUAL) as TOTAL_JUAL 
                FROM penjualan 
                WHERE IDPRODUK = '$produk_id' 
                GROUP BY TAHUN, BULAN 
                ORDER BY TAHUN, BULAN";
$result_sales = mysqli_query($conn, $query_sales);

// Prepare data untuk perhitungan dan chart
$sales_data = [];
$chart_labels = [];
$chart_actual = [];
$chart_predicted = [];

if ($result_sales && mysqli_num_rows($result_sales) > 0) {
    while ($row = mysqli_fetch_assoc($result_sales)) {
        $sales_data[] = [
            'bulan' => $row['BULAN'],
            'tahun' => $row['TAHUN'],
            'total' => (float)$row['TOTAL_JUAL']
        ];
        
        // Buat label chart (nama bulan + tahun)
        $chart_labels[] = $bulan_nama[(int)$row['BULAN']] . ' ' . $row['TAHUN'];
        $chart_actual[] = (float)$row['TOTAL_JUAL'];
    }
}

// Lakukan perhitungan SES
function calculateSES($sales_data, $alpha) {
    $smoothed_values = [];
    $predictions = [];
    $errors = [];
    
    // Nilai awal untuk pemulusan
    $smoothed_values[0] = $sales_data[0]['total'];
    
    // Hitung untuk setiap periode
    for ($i = 1; $i < count($sales_data); $i++) {
        $actual = $sales_data[$i]['total'];
        $previous_smoothed = $smoothed_values[$i-1];
        
        // Prediksi untuk periode ini
        $prediction = $previous_smoothed;
        $predictions[$i] = $prediction;
        
        // Nilai pemulusan periode ini
        $current_smoothed = $alpha * $actual + (1 - $alpha) * $previous_smoothed;
        $smoothed_values[$i] = $current_smoothed;
        
        // Hitung error
        $error = $actual - $prediction;
        $percentage_error = $actual != 0 ? (abs($error) / $actual) * 100 : 0;
        
        $errors[$i] = [
            'prediction' => $prediction,
            'error' => $error,
            'percentage_error' => $percentage_error
        ];
        
        // Tambahkan ke data chart
        $chart_predicted[] = $prediction;
    }
    
    // Prediksi untuk periode berikutnya
    $next_forecast = $smoothed_values[count($smoothed_values) - 1];
    
    // Hitung MAPE
    $total_percentage_error = 0;
    $count = 0;
    
    for ($i = 1; $i < count($sales_data); $i++) {
        $total_percentage_error += $errors[$i]['percentage_error'];
        $count++;
    }
    
    $mape = $count > 0 ? $total_percentage_error / $count : 0;
    
    return [
        'next_forecast' => $next_forecast,
        'smoothed_values' => $smoothed_values,
        'errors' => $errors,
        'mape' => $mape
    ];
}

// Lakukan perhitungan jika data penjualan tersedia
$result_to_use = null;
if (count($sales_data) >= 2) {
    $result_to_use = calculateSES($sales_data, $prediksi['ALPHA']);
    
    // Tambahkan nilai prediksi periode berikutnya ke chart
    $chart_labels[] = $periode_display;
    $chart_actual[] = null;
    $chart_predicted[] = $prediksi['NILAI_PREDIKSI'];
}

// Buat data chart untuk JavaScript
$chart_data = json_encode([
    'labels' => $chart_labels,
    'actual' => $chart_actual,
    'predicted' => $chart_predicted
]);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Detail Prediksi Penjualan</title>
    <link rel="stylesheet" href="../assets/css/bootstrap.min.css">
    <link rel="stylesheet" href="../assets/fontawesome/css/all.min.css">
    <style>
        body {
        background: linear-gradient(135deg, #0f172a 0%, #1e293b 60%, #334155 100%);
        background-attachment: fixed;
        min-height: 100vh;
        }
        .table-scrollable {
            max-height: 500px;
            overflow-y: auto;
        }
        .chart-container {
            min-height: 400px;
            width: 100%;
            margin-top: 20px;
        }
    </style>
</head>
<body class="bg-light">
<?php include "header.php"; ?>

<div class="container mt-4">
    <!-- Header Section -->
    <div class="row mb-3">
        <div class="col-12">
            <div class="card shadow">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="card-title">Detail Prediksi Penjualan</h5>
                        <a href="riwayat_prediksi.php" class="btn btn-secondary btn-sm">
                            <i class="fas fa-arrow-left"></i> Kembali
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Info Section -->
    <div class="row mb-3">
        <div class="col-md-6">
            <div class="card shadow h-100">
                <div class="card-header bg-primary text-white">
                    <h6 class="m-0 font-weight-bold">Informasi Prediksi</h6>
                </div>
                <div class="card-body">
                    <table class="table table-borderless">
                        <tr>
                            <td><strong>ID Prediksi</strong></td>
                            <td>: <?= $prediksi['IDPRED'] ?></td>
                        </tr>
                        <tr>
                            <td><strong>Waktu Prediksi</strong></td>
                            <td>: <?= date('d/m/Y H:i', strtotime($prediksi['WAKTUPRED'])) ?></td>
                        </tr>
                        <tr>
                            <td><strong>Produk</strong></td>
                            <td>: <?= $display_produk ?></td>
                        </tr>
                        <tr>
                            <td><strong>Periode Prediksi</strong></td>
                            <td>: <?= $periode_display ?></td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card shadow h-100">
                <div class="card-header bg-success text-white">
                    <h6 class="m-0 font-weight-bold">Hasil Prediksi</h6>
                </div>
                <div class="card-body">
                    <table class="table table-borderless">
                        <tr>
                            <td><strong>Nilai Prediksi</strong></td>
                            <td>: <span class="badge bg-success p-2"><?= number_format($prediksi['NILAI_PREDIKSI'], 2) ?> unit</span></td>
                        </tr>
                        <tr>
                            <td><strong>Alpha</strong></td>
                            <td>: <?= number_format($prediksi['ALPHA'], 2) ?></td>
                        </tr>
                        <tr>
                            <td><strong>MAPE</strong></td>
                            <td>: <?= number_format($prediksi['MAPE'], 2) ?>%</td>
                        </tr>
                    </table>
                    <div class="alert alert-info mt-2">
                        <i class="fas fa-info-circle me-2"></i>
                        Berdasarkan hasil perhitungan dengan metode Single Exponential Smoothing, prediksi penjualan untuk 
                        <strong><?= $display_produk ?></strong> pada periode <strong><?= $periode_display ?></strong> adalah sebanyak 
                        <strong><?= number_format($prediksi['NILAI_PREDIKSI'], 2) ?> unit</strong>.
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Chart Section -->
    <div class="row mb-3">
        <div class="col-12">
            <div class="card shadow">
                <div class="card-header bg-primary text-white">
                    <h6 class="m-0 font-weight-bold">Grafik Penjualan Aktual dan Prediksi</h6>
                </div>
                <div class="card-body">
                    <div class="chart-container">
                        <canvas id="salesChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Calculation Section -->
    <?php if ($result_to_use): ?>
    <div class="row mb-3">
        <div class="col-12">
            <div class="card shadow">
                <div class="card-header bg-primary text-white">
                    <h6 class="m-0 font-weight-bold">Tabel Perhitungan SES (Alpha = <?= number_format($prediksi['ALPHA'], 2) ?>)</h6>
                </div>
                <div class="card-body">
                    <div class="table-responsive table-scrollable">
                        <table class="table table-bordered table-hover">
                            <thead class="table-primary">
                                <tr>
                                    <th>No</th>
                                    <th>Periode</th>
                                    <th>Penjualan Aktual (Xt)</th>
                                    <th>Nilai Pemulusan (St)</th>
                                    <th>Error (Xt - F[t-1])</th>
                                    <th>MAPE (%)</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                // First row is special case (no error calculation)
                                $first_period = $bulan_nama[$sales_data[0]['bulan']] . " " . $sales_data[0]['tahun'];
                                ?>
                                <tr>
                                    <td>1</td>
                                    <td><?= $first_period ?></td>
                                    <td><?= number_format($sales_data[0]['total'], 2) ?></td>
                                    <td><?= number_format($result_to_use['smoothed_values'][0], 2) ?></td>
                                    <td>-</td>
                                    <td>-</td>
                                </tr>
                                <?php
                                for ($i = 1; $i < count($sales_data); $i++): 
                                    $period = $bulan_nama[$sales_data[$i]['bulan']] . " " . $sales_data[$i]['tahun'];
                                ?>
                                <tr>
                                    <td><?= $i + 1 ?></td>
                                    <td><?= $period ?></td>
                                    <td><?= number_format($sales_data[$i]['total'], 2) ?></td>
                                    <td><?= number_format($result_to_use['smoothed_values'][$i], 2) ?></td>
                                    <td><?= number_format($result_to_use['errors'][$i]['error'], 2) ?></td>
                                    <td><?= number_format($result_to_use['errors'][$i]['percentage_error'], 2) ?></td>
                                </tr>
                                <?php endfor; ?>
                            </tbody>
                            <tfoot class="table-secondary">
                                <tr>
                                    <th colspan="5">Rata-rata</th>
                                    <th>MAPE: <?= number_format($result_to_use['mape'], 2) ?>%</th>
                                </tr>
                                <tr class="table-success">
                                    <th colspan="2">Prediksi untuk <?= $display_produk ?> - <?= $periode_display ?></th>
                                    <th><?= number_format($prediksi['NILAI_PREDIKSI'], 2) ?> unit</th>
                                    <th colspan="3"></th>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php else: ?>
    <div class="row mb-3">
        <div class="col-12">
            <div class="card shadow">
                <div class="card-body">
                    <div class="alert alert-warning">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        Data perhitungan detail tidak tersedia. Kemungkinan data penjualan tidak cukup untuk menampilkan tabel perhitungan.
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>
    
    <!-- Single Exponential Smoothing Info -->
    <div class="row mb-3">
        <div class="col-md-6">
            <div class="card shadow">
                <div class="card-header bg-info text-white">
                    <h6 class="m-0 font-weight-bold">Single Exponential Smoothing</h6>
                </div>
                <div class="card-body">
                    <p>Rumus:</p>
                    <pre>St = α * Xt + (1 - α) * St-1</pre>
                    <p>Keterangan:
                        <br>- St: Nilai pemulusan periode saat ini
                        <br>- α: Konstanta pemulusan (0.1-0.9)
                        <br>- Xt: Nilai aktual periode saat ini
                        <br>- St-1: Nilai pemulusan periode sebelumnya
                    </p>
                    <p>Prediksi untuk periode berikutnya (t+1) = St</p>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card shadow">
                <div class="card-header bg-info text-white">
                    <h6 class="m-0 font-weight-bold">Metrik Error</h6>
                </div>
                <div class="card-body">
                    <p>MAPE (Mean Absolute Percentage Error)</p>
                    <pre>MAPE = (1/n) * ∑ |Actual-Forecast|/Actual * 100%</pre>
                    <p>Interpretasi nilai MAPE:
                        <br>- < 10%: Sangat Baik
                        <br>- 10-20%: Baik
                        <br>- 20-50%: Cukup
                        <br>- > 50%: Buruk
                    </p>
                    <p>Semakin rendah nilai MAPE, semakin baik akurasi prediksi.</p>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="../assets/js/bootstrap.bundle.min.js"></script>
<!-- Chart.js -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/3.7.1/chart.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    <?php if (!empty($chart_data)): ?>
    var ctx = document.getElementById('salesChart').getContext('2d');
    var chartData = <?= $chart_data ?>;
    
    var salesChart = new Chart(ctx, {
        type: 'line',
        data: {
            labels: chartData.labels,
            datasets: [
                {
                    label: 'Penjualan Aktual',
                    data: chartData.actual,
                    backgroundColor: 'rgba(54, 162, 235, 0.2)',
                    borderColor: 'rgba(54, 162, 235, 1)',
                    borderWidth: 2,
                    pointRadius: 4,
                    tension: 0.1
                },
                {
                    label: 'Prediksi',
                    data: chartData.predicted,
                    backgroundColor: 'rgba(255, 99, 132, 0.2)',
                    borderColor: 'rgba(255, 99, 132, 1)',
                    borderWidth: 2,
                    pointRadius: 4,
                    tension: 0.1,
                    borderDash: [5, 5]
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                y: {
                    beginAtZero: false,
                    title: {
                        display: true,
                        text: 'Jumlah Penjualan (unit)'
                    }
                },
                x: {
                    title: {
                        display: true,
                        text: 'Periode'
                    }
                }
            },
            plugins: {
                title: {
                    display: true,
                    text: 'Perbandingan Data Aktual dan Prediksi'
                },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            var label = context.dataset.label || '';
                            if (label) {
                                label += ': ';
                            }
                            if (context.parsed.y !== null) {
                                label += Number(context.parsed.y).toFixed(2) + ' unit';
                            }
                            return label;
                        }
                    }
                }
            }
        }
    });
    <?php endif; ?>
});
</script>
</body>
</html>