<?php
session_start();
require '../config.php';

if (!isset($_SESSION['log']) || $_SESSION['role'] !== 'admin') {
    echo "Akses ditolak.";
    exit();
}

$message = '';
$status = '';

// Ambil data produk
$query_produk = "SELECT IDPRODUK, NAMAPRODUK FROM produk WHERE JUMLAHPRODUK > 0 ORDER BY NAMAPRODUK";
$result_produk = mysqli_query($conn, $query_produk);

if (!$result_produk) {
    $message = "Error: " . mysqli_error($conn);
    $status = "danger";
}

$bulan_nama = [
    1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
    5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
    9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'
];

function getLatestPeriod($conn, $produk_id) {
    $query = "SELECT BULAN, TAHUN FROM penjualan 
            WHERE IDPRODUK = '$produk_id' 
            ORDER BY TAHUN DESC, BULAN DESC 
            LIMIT 1";
    $result = mysqli_query($conn, $query);
    
    if ($result && mysqli_num_rows($result) > 0) {
        return mysqli_fetch_assoc($result);
    }
    return false;
}

function getNextPeriod($month, $year) {
    if ($month == 12) {
        return ['bulan' => 1, 'tahun' => $year + 1];
    } else {
        return ['bulan' => $month + 1, 'tahun' => $year];
    }
}

// Fungsi Double Exponential Smoothing (Holt) - HANYA MAPE
function calculateDES($sales_data, $alpha, $beta) {
    $predictions = [];
    $errors = [];
    $levels = [];
    $trends = [];
    
    $n = count($sales_data);
    
    // Inisialisasi
    $levels[0] = $sales_data[0]['total'];
    $trends[0] = ($n > 1) ? ($sales_data[1]['total'] - $sales_data[0]['total']) : 0;
    
    for ($i = 1; $i < $n; $i++) {
        $actual = $sales_data[$i]['total'];
        
        // Prediksi (F)
        $prediction = $levels[$i-1] + $trends[$i-1];
        
        // Level (L)
        $levels[$i] = $alpha * $actual + (1 - $alpha) * ($levels[$i-1] + $trends[$i-1]);
        
        // Trend (T)
        $trends[$i] = $beta * ($levels[$i] - $levels[$i-1]) + (1 - $beta) * $trends[$i-1];
        
        // Hitung error & MAPE
        $error = $actual - $prediction;
        $percentage_error = $actual != 0 ? (abs($error) / $actual) * 100 : 0;
        
        $errors[$i] = [
            'prediction' => $prediction,
            'error' => $error,
            'percentage_error' => $percentage_error
        ];
    }
    
    // Prediksi bulan depan
    $next_forecast = $levels[$n - 1] + $trends[$n - 1];
    
    $total_percentage_error = 0;
    $count = 0;
    
    for ($i = 1; $i < $n; $i++) {
        $total_percentage_error += $errors[$i]['percentage_error'];
        $count++;
    }
    
    $mape = $count > 0 ? $total_percentage_error / $count : 0;
    $accuracy = 100 - $mape;
    
    return [
        'next_forecast' => $next_forecast,
        'levels' => $levels,
        'trends' => $trends,
        'errors' => $errors,
        'mape' => $mape,
        'accuracy' => $accuracy
    ];
}

$available_months = [];
$available_years = [];
$latest_period = null;
$next_period = null;

if (isset($_POST['check_produk']) || isset($_POST['prediksi'])) {
    $produk_id = $_POST['produk_id'];
    $latest_period = getLatestPeriod($conn, $produk_id);
    
    if ($latest_period) {
        $next_period = getNextPeriod($latest_period['BULAN'], $latest_period['TAHUN']);
        $available_months = [$next_period['bulan']];
        $available_years = [$next_period['tahun']];
    }
}

$param_results = [];
$best_alpha = null;
$best_beta = null;
$best_result = null;
$sales_data = [];

// Data untuk Grafik Javascript
$chart_labels = [];
$chart_aktual = [];
$chart_prediksi = [];

if (isset($_POST['prediksi'])) {
    $produk_id = $_POST['produk_id'];
    $bulan_prediksi = $_POST['bulan'];
    $tahun_prediksi = $_POST['tahun'];
    
    $find_best_param = isset($_POST['find_best_param']) ? true : false;
    $selected_alpha = isset($_POST['alpha']) ? (float)$_POST['alpha'] : 0.5;
    $selected_beta = isset($_POST['beta']) ? (float)$_POST['beta'] : 0.5;
    
    if ($latest_period) {
        $next_period = getNextPeriod($latest_period['BULAN'], $latest_period['TAHUN']);
        
        if ($bulan_prediksi != $next_period['bulan'] || $tahun_prediksi != $next_period['tahun']) {
            $message = "Hanya boleh memprediksi untuk bulan berikutnya dari data terakhir.";
            $status = "warning";
        } else {
            $query_produk_name = "SELECT NAMAPRODUK FROM produk WHERE IDPRODUK = '$produk_id'";
            $result_produk_name = mysqli_query($conn, $query_produk_name);
            $produk_name = mysqli_fetch_assoc($result_produk_name)['NAMAPRODUK'];
            
            $query_sales = "SELECT BULAN, TAHUN, SUM(JUMLAHJUAL) as TOTAL_JUAL 
                            FROM penjualan 
                            WHERE IDPRODUK = '$produk_id' 
                            GROUP BY TAHUN, BULAN 
                            ORDER BY TAHUN, BULAN";
            $result_sales = mysqli_query($conn, $query_sales);
            
            if (!$result_sales) {
                $message = "Error: " . mysqli_error($conn);
                $status = "danger";
            } else {
                if (mysqli_num_rows($result_sales) < 3) {
                    $message = "Data penjualan tidak cukup. Minimal diperlukan 3 bulan data untuk DES Holt.";
                    $status = "warning";
                } else {
                    $sales_data = [];
                    while ($row = mysqli_fetch_assoc($result_sales)) {
                        $sales_data[] = [
                            'bulan' => $row['BULAN'],
                            'tahun' => $row['TAHUN'],
                            'total' => (float)$row['TOTAL_JUAL']
                        ];
                    }
                    
                    if ($find_best_param) {
                        $highest_accuracy = -INF;
                        for ($alpha_test = 0.1; $alpha_test <= 0.9; $alpha_test += 0.1) {
                            for ($beta_test = 0.1; $beta_test <= 0.9; $beta_test += 0.1) {
                                $alpha_test = round($alpha_test, 1);
                                $beta_test = round($beta_test, 1);
                                
                                $result = calculateDES($sales_data, $alpha_test, $beta_test);
                                $param_results["$alpha_test|$beta_test"] = $result;
                                
                                if ($result['accuracy'] > $highest_accuracy) {
                                    $highest_accuracy = $result['accuracy'];
                                    $best_alpha = $alpha_test;
                                    $best_beta = $beta_test;
                                    $best_result = $result;
                                }
                            }
                        }
                        $result_to_use = $best_result;
                        $alpha_to_use = $best_alpha;
                        $beta_to_use = $best_beta;
                    } else {
                        $result = calculateDES($sales_data, $selected_alpha, $selected_beta);
                        $result_to_use = $result;
                        $alpha_to_use = $selected_alpha;
                        $beta_to_use = $selected_beta;
                    }
                    
                    $message = "Prediksi penjualan " . $produk_name . " pada " . $bulan_nama[$bulan_prediksi] . " " . $tahun_prediksi . 
                               " adalah <strong>" . number_format($result_to_use['next_forecast'], 2) . " unit</strong> (Alpha " . $alpha_to_use . ", Beta " . $beta_to_use . ")";
                    $status = "success";
                    $periode_prediksi = $bulan_prediksi . "-" . $tahun_prediksi;
                    
                    $_SESSION['prediction_data'] = [
                        'produk_id' => $produk_id,
                        'periode_prediksi' => $periode_prediksi,
                        'next_forecast' => $result_to_use['next_forecast'],
                        'alpha' => $alpha_to_use,
                        'beta' => $beta_to_use,
                        'mape' => $result_to_use['mape'],
                        'accuracy' => $result_to_use['accuracy']
                    ];

                    // --- PERSIAPAN DATA UNTUK GRAFIK ---
                    for ($i = 0; $i < count($sales_data); $i++) {
                        $chart_labels[] = $bulan_nama[$sales_data[$i]['bulan']] . " " . $sales_data[$i]['tahun'];
                        $chart_aktual[] = $sales_data[$i]['total'];
                        
                        if ($i == 0) {
                            $chart_prediksi[] = null;
                        } else {
                            $chart_prediksi[] = round($result_to_use['errors'][$i]['prediction'], 2);
                        }
                    }
                    $chart_labels[] = $bulan_nama[$bulan_prediksi] . " " . $tahun_prediksi . " (Prediksi)";
                    $chart_aktual[] = null;
                    $chart_prediksi[] = round($result_to_use['next_forecast'], 2);
                }
            }
        }
    }
}

// PASTIKAN STRUKTUR TABEL DATABASE SESUAI
if (isset($_POST['simpan_prediksi']) && isset($_SESSION['prediction_data'])) {
    $data = $_SESSION['prediction_data'];
    $query_insert = "INSERT INTO pred (WAKTUPRED, HASILPRED, PREDIKSIBULANTAHUN, NILAI_PREDIKSI, ALPHA, BETA, MAPE, AKURASI) 
                    VALUES (NOW(), '{$data['produk_id']}', '{$data['periode_prediksi']}', {$data['next_forecast']}, 
                    {$data['alpha']}, {$data['beta']}, {$data['mape']}, {$data['accuracy']})";
    
    if (mysqli_query($conn, $query_insert)) {
        $message = "Hasil prediksi berhasil disimpan!";
        $status = "success";
        unset($_SESSION['prediction_data']);
    } else {
        $message = "Error menyimpan data: " . mysqli_error($conn);
        $status = "danger";
    }
}

include "header.php"; 
?>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Data Penjualan - Admin</title>
    <link rel="stylesheet" href="../assets/css/bootstrap.min.css">
    <link rel="stylesheet" href="../assets/fontawesome/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/style.css">
<style>
    .table-scrollable { max-height: 500px; overflow-y: auto; }
    .right-body{
            background: #FBF7E6;
            background-attachment: fixed;
            min-height: 100vh;}
    .interpretation-card {
        border-left: 5px solid #28a745;
        background-color: #f8fff9;
    }
</style>
</head>
<body class="right-body">
<div class="container mt-4 mb-5">
    <div class="row">
        <div class="col-lg-8">
            <div class="card shadow">
                <div class="card-body">
                    <h5 class="card-title">Prediksi Penjualan (Double Exponential Smoothing)</h5>
                    <?php if ($message): ?>
                        <div class="alert alert-<?= $status ?>"><?= $message ?></div>
                    <?php endif; ?>

                    <form method="post">
                        <div class="mb-3">
                            <label for="produk_id" class="form-label">Pilih Produk</label>
                            <select id="produk_id" name="produk_id" class="form-select" required>
                                <?php 
                                if ($result_produk && mysqli_num_rows($result_produk) > 0) {
                                    mysqli_data_seek($result_produk, 0);
                                    while ($row = mysqli_fetch_assoc($result_produk)) : 
                                        $selected = isset($_POST['produk_id']) && $_POST['produk_id'] == $row['IDPRODUK'] ? 'selected' : '';
                                ?>
                                    <option value="<?= $row['IDPRODUK'] ?>" <?= $selected ?>><?= $row['NAMAPRODUK'] ?></option>
                                <?php 
                                    endwhile; 
                                }
                                ?>
                            </select>
                        </div>

                        <button type="submit" name="check_produk" class="btn btn-secondary mb-3">
                            <i class="fas fa-check"></i> Cek Periode
                        </button>

                        <?php if ($latest_period && $next_period): ?>
                            <div class="alert alert-info">
                                Data terakhir: <?= $bulan_nama[$latest_period['BULAN']] ?> <?= $latest_period['TAHUN'] ?>.<br>
                                Prediksi untuk: <strong><?= $bulan_nama[$next_period['bulan']] ?> <?= $next_period['tahun'] ?></strong>
                            </div>

                            <input type="hidden" name="bulan" value="<?= $next_period['bulan'] ?>">
                            <input type="hidden" name="tahun" value="<?= $next_period['tahun'] ?>">

                            <div class="mb-3">
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" id="find_best_param" name="find_best_param">
                                    <label class="form-check-label" for="find_best_param">Cari Alpha & Beta Terbaik Otomatis</label>
                                </div>
                            </div>

                            <div class="row" id="parameter_selection">
                                <div class="col-md-6 mb-3">
                                    <label for="alpha" class="form-label">Alpha (Level/Data Dasar)</label>
                                    <select id="alpha" name="alpha" class="form-select">
                                        <?php for ($a = 0.1; $a <= 0.9; $a += 0.1): ?>
                                            <option value="<?= number_format($a, 1) ?>" <?= $a == 0.5 ? 'selected' : '' ?>><?= number_format($a, 1) ?></option>
                                        <?php endfor; ?>
                                    </select>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="beta" class="form-label">Beta (Trend)</label>
                                    <select id="beta" name="beta" class="form-select">
                                        <?php for ($b = 0.1; $b <= 0.9; $b += 0.1): ?>
                                            <option value="<?= number_format($b, 1) ?>" <?= $b == 0.2 ? 'selected' : '' ?>><?= number_format($b, 1) ?></option>
                                        <?php endfor; ?>
                                    </select>
                                </div>
                            </div>

                            <button type="submit" name="prediksi" class="btn btn-primary">
                                <i class="fas fa-chart-line"></i> Hitung Prediksi
                            </button>
                        <?php endif; ?>
                        
                        <a href="riwayat_prediksi.php" class="btn btn-info ms-2">
                            <i class="fas fa-history"></i> Riwayat
                        </a>
                    </form>
                    
                    <?php if (isset($_SESSION['prediction_data'])): ?>
                    <form method="post" class="mt-3">
                        <button type="submit" name="simpan_prediksi" class="btn btn-success">
                            <i class="fas fa-save"></i> Simpan Prediksi
                        </button>
                    </form>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card shadow mb-4">
                <div class="card-body">
                    <h5 class="card-title text-primary"><i class="fas fa-book"></i> Metode DES Holt</h5>
                    <p class="text-sm text-muted"><strong>Double Exponential Smoothing</strong> digunakan meramalkan data penjualan yang memiliki <strong>Tren</strong> (kecenderungan naik/turun).</p>
                    
                    <h6 class="font-weight-bold">1. Parameter Alpha (α)</h6>
                    <p class="text-sm">Pemulusan untuk <strong>Nilai Dasar (Level)</strong> penjualan. Menentukan seberapa cepat nilai bereaksi terhadap data baru.</p>
                    
                    <h6 class="font-weight-bold">2. Parameter Beta (β)</h6>
                    <p class="text-sm">Pemulusan untuk <strong>Pergerakan (Tren)</strong>. Menentukan seberapa cepat sistem merespons perubahan arah tren.</p>
                    
                    <hr>
                    <p class="text-sm mb-1"><strong>Rumus:</strong></p>
                    <div class="bg-light p-2 border rounded mb-2" style="font-size: 0.85rem;">
                        <span class="text-primary">Level:</span> Lt = α*Yt + (1-α)*(L<sub>t-1</sub> + T<sub>t-1</sub>)<br>
                        <span class="text-success">Trend:</span> Tt = β*(Lt - L<sub>t-1</sub>) + (1-β)*T<sub>t-1</sub><br>
                        <span class="text-danger">Prediksi:</span> F<sub>t+1</sub> = Lt + Tt
                    </div>
                </div>
            </div>
            
            <div class="card shadow">
                <div class="card-body">
                    <h5 class="card-title text-info"><i class="fas fa-bullseye"></i> Indikator Error</h5>
                    <p class="text-sm">Evaluasi akurasi menggunakan metode <strong>MAPE (Mean Absolute Percentage Error)</strong>. MAPE menghitung besarnya persentase penyimpangan (error) antara data hasil prediksi dengan data aktual. Semakin kecil persentase MAPE, semakin akurat hasil prediksinya.</p>
                </div>
            </div>
        </div>
    </div>
    
    <?php if (isset($result_to_use)): ?>
    <div class="row mt-4">
        
        <div class="col-lg-12 mb-4">
            <div class="card shadow interpretation-card">
                <div class="card-body">
                    <h5 class="card-title text-success"><i class="fas fa-lightbulb"></i> Interpretasi Hasil Prediksi</h5>
                    
                    <?php
                    $last_level = end($result_to_use['levels']);
                    $last_trend = end($result_to_use['trends']);
                    
                    if ($last_trend > 0) {
                        $trend_direction = "menunjukkan tren positif (naik)";
                        $trend_color = "text-success";
                        $icon = "fa-arrow-up";
                    } elseif ($last_trend < 0) {
                        $trend_direction = "menunjukkan tren negatif (turun)";
                        $trend_color = "text-danger";
                        $icon = "fa-arrow-down";
                    } else {
                        $trend_direction = "cenderung stabil/datar";
                        $trend_color = "text-secondary";
                        $icon = "fa-minus";
                    }
                    ?>
                    
                    <p class="mb-2">Peramalan untuk <strong><?= $bulan_nama[$bulan_prediksi] . " " . $tahun_prediksi ?></strong> menghasilkan angka <strong><?= number_format($result_to_use['next_forecast'], 2) ?> unit</strong>. Angka ini didapat dari:</p>
                    <ul>
                        <li><strong>Level (Nilai Dasar):</strong> Pemulusan data penjualan terakhir berada pada level <strong><?= number_format($last_level, 2) ?> unit</strong>.</li>
                        <li><strong>Tren (Pergerakan):</strong> Data penjualan saat ini terpantau <strong class="<?= $trend_color ?>"><i class="fas <?= $icon ?>"></i> <?= $trend_direction ?></strong> dengan nilai pergerakan sebesar <strong><?= number_format($last_trend, 2) ?></strong>.</li>
                    </ul>
                    <p class="mb-0 text-muted" style="font-size: 0.9rem;"><em>*(Rumus: Nilai Prediksi = Level Terakhir + Tren Terakhir)</em></p>
                </div>
            </div>
        </div>

        <div class="col-lg-12 mb-4">
            <div class="card shadow">
                <div class="card-body">
                    <h5 class="card-title">Grafik Prediksi vs Aktual</h5>
                    <div style="height: 400px; width: 100%;">
                        <canvas id="prediksiChart"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-12">
            <div class="card shadow">
                <div class="card-body">
                    <h5 class="card-title">Tabel Perhitungan Lengkap (Alpha = <?= $alpha_to_use ?>, Beta = <?= $beta_to_use ?>)</h5>
                    <div class="table-responsive table-scrollable">
                        <table class="table table-bordered table-hover text-center align-middle">
                            <thead class="table-primary">
                                <tr>
                                    <th>No</th>
                                    <th>Periode</th>
                                    <th>Aktual (Y)</th>
                                    <th>Level (L)</th>
                                    <th>Trend (T)</th>
                                    <th>Prediksi (F)</th>
                                    <th>Error</th>
                                    <th>MAPE (%)</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $first_period = $bulan_nama[$sales_data[0]['bulan']] . " " . $sales_data[0]['tahun'];
                                ?>
                                <tr>
                                    <td>1</td>
                                    <td><?= $first_period ?></td>
                                    <td><?= $sales_data[0]['total'] ?></td>
                                    <td><?= number_format($result_to_use['levels'][0], 2) ?></td>
                                    <td><?= number_format($result_to_use['trends'][0], 2) ?></td>
                                    <td>-</td>
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
                                    <td><?= $sales_data[$i]['total'] ?></td>
                                    <td><?= number_format($result_to_use['levels'][$i], 2) ?></td>
                                    <td><?= number_format($result_to_use['trends'][$i], 2) ?></td>
                                    <td><?= number_format($result_to_use['errors'][$i]['prediction'], 2) ?></td>
                                    <td><?= number_format($result_to_use['errors'][$i]['error'], 2) ?></td>
                                    <td><?= number_format($result_to_use['errors'][$i]['percentage_error'], 2) ?></td>
                                </tr>
                                <?php endfor; ?>
                            </tbody>
                            <tfoot class="table-secondary font-weight-bold">
                                <tr>
                                    <th colspan="7" class="text-end">Rata-rata Akurasi:</th>
                                    <th><?= number_format($result_to_use['mape'], 2) ?>%</th>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        
    </div>
    <?php endif; ?>
</div>

</div>
        </div>
    </div>

<script src="../assets/js/jquery.min.js"></script>
<script src="../assets/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Logika form Alpha Beta
    const findBestParamCheckbox = document.getElementById('find_best_param');
    const parameterSelection = document.getElementById('parameter_selection');
    
    if (findBestParamCheckbox && parameterSelection) {
        findBestParamCheckbox.addEventListener('change', function() {
            if (this.checked) {
                parameterSelection.style.display = 'none';
            } else {
                parameterSelection.style.display = 'flex';
            }
        });
    }

    // --- LOGIKA MENGGAMBAR GRAFIK CHART.JS ---
    <?php if (isset($result_to_use)): ?>
        const ctx = document.getElementById('prediksiChart').getContext('2d');
        
        const labels = <?= json_encode($chart_labels) ?>;
        const dataAktual = <?= json_encode($chart_aktual) ?>;
        const dataPrediksi = <?= json_encode($chart_prediksi) ?>;

        new Chart(ctx, {
            type: 'line',
            data: {
                labels: labels,
                datasets: [
                    {
                        label: 'Penjualan Aktual',
                        data: dataAktual,
                        borderColor: 'rgb(54, 162, 235)', 
                        backgroundColor: 'rgba(54, 162, 235, 0.1)',
                        borderWidth: 2,
                        pointRadius: 4,
                        pointBackgroundColor: 'rgb(54, 162, 235)',
                        fill: true,
                        tension: 0.1 
                    },
                    {
                        label: 'Hasil Prediksi (DES)',
                        data: dataPrediksi,
                        borderColor: 'rgb(255, 99, 132)', 
                        borderWidth: 2,
                        borderDash: [5, 5], 
                        pointRadius: 5,
                        pointBackgroundColor: 'rgb(255, 99, 132)',
                        pointStyle: 'rectRot', 
                        fill: false,
                        tension: 0.1
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'top',
                    },
                    tooltip: {
                        mode: 'index',
                        intersect: false,
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        title: {
                            display: true,
                            text: 'Jumlah Unit'
                        }
                    },
                    x: {
                        title: {
                            display: true,
                            text: 'Periode Bulan'
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