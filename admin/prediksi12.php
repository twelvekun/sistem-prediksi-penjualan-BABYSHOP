<?php
session_start();
require '../config.php';

// Cek apakah user sudah login dan memiliki peran admin
if (!isset($_SESSION['log']) || $_SESSION['role'] !== 'admin') {
    echo "Akses ditolak.";
    exit();
}

// Initialize variables
$message = '';
$status = '';

// Get products for select dropdown
$query_produk = "SELECT IDPRODUK, NAMAPRODUK FROM produk WHERE JUMLAHPRODUK > 0 ORDER BY NAMAPRODUK";
$result_produk = mysqli_query($conn, $query_produk);

if (!$result_produk) {
    $message = "Error: " . mysqli_error($conn);
    $status = "danger";
}

// Define month names
$bulan_nama = [
    1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
    5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
    9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'
];

// Function to get latest available data period
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

// Function to get next month/year
function getNextPeriod($month, $year) {
    if ($month == 12) {
        return ['bulan' => 1, 'tahun' => $year + 1];
    } else {
        return ['bulan' => $month + 1, 'tahun' => $year];
    }
}

// Function to calculate DES (Holt's Method)
function calculateDES($sales_data, $alpha, $beta) {
    $predictions = [];
    $errors = [];
    $levels = [];
    $trends = [];
    
    $n = count($sales_data);
    
    // Inisialisasi untuk periode pertama
    $levels[0] = $sales_data[0]['total'];
    // Trend awal diasumsikan dari selisih data ke-2 dan ke-1 (jika ada)
    $trends[0] = ($n > 1) ? ($sales_data[1]['total'] - $sales_data[0]['total']) : 0;
    
    // Hitung untuk setiap periode menggunakan metode DES Holt
    for ($i = 1; $i < $n; $i++) {
        $actual = $sales_data[$i]['total'];
        
        // Prediksi periode saat ini dibuat dari Level dan Trend periode sebelumnya
        $prediction = $levels[$i-1] + $trends[$i-1];
        
        // Pemulusan Level (Lt)
        // Lt = α * Yt + (1 - α) * (L_t-1 + T_t-1)
        $levels[$i] = $alpha * $actual + (1 - $alpha) * ($levels[$i-1] + $trends[$i-1]);
        
        // Pemulusan Trend (Tt)
        // Tt = β * (Lt - L_t-1) + (1 - β) * T_t-1
        $trends[$i] = $beta * ($levels[$i] - $levels[$i-1]) + (1 - $beta) * $trends[$i-1];
        
        // Hitung error
        $error = $actual - $prediction;
        $abs_error = abs($error);
        $squared_error = pow($error, 2);
        $percentage_error = $actual != 0 ? (abs($error) / $actual) * 100 : 0;
        
        $errors[$i] = [
            'prediction' => $prediction,
            'error' => $error,
            'abs_error' => $abs_error,
            'squared_error' => $squared_error,
            'percentage_error' => $percentage_error
        ];
    }
    
    // Prediksi untuk periode berikutnya (m = 1)
    // F_t+1 = Lt + Tt
    $next_forecast = $levels[$n - 1] + $trends[$n - 1];
    
    // Hitung metrik akurasi
    $total_abs_error = 0;
    $total_squared_error = 0;
    $total_percentage_error = 0;
    $count = 0;
    
    for ($i = 1; $i < $n; $i++) {
        $total_abs_error += $errors[$i]['abs_error'];
        $total_squared_error += $errors[$i]['squared_error'];
        $total_percentage_error += $errors[$i]['percentage_error'];
        $count++;
    }
    
    $mad = $count > 0 ? $total_abs_error / $count : 0;
    $mse = $count > 0 ? $total_squared_error / $count : 0;
    $mape = $count > 0 ? $total_percentage_error / $count : 0;
    $accuracy = 100 - $mape;
    
    return [
        'next_forecast' => $next_forecast,
        'levels' => $levels,
        'trends' => $trends,
        'errors' => $errors,
        'mad' => $mad,
        'mse' => $mse,
        'mape' => $mape,
        'accuracy' => $accuracy
    ];
}

// Initialize available_months and available_years arrays
$available_months = [];
$available_years = [];
$latest_period = null;
$next_period = null;

// If product is selected, get available prediction periods
if (isset($_POST['check_produk']) || isset($_POST['prediksi'])) {
    $produk_id = $_POST['produk_id'];
    $latest_period = getLatestPeriod($conn, $produk_id);
    
    if ($latest_period) {
        $next_period = getNextPeriod($latest_period['BULAN'], $latest_period['TAHUN']);
        $available_months = [$next_period['bulan']];
        $available_years = [$next_period['tahun']];
    }
}

// Initialize array to store all parameter results
$param_results = [];
$best_alpha = null;
$best_beta = null;
$best_result = null;
$sales_data = [];

// Process prediction request
if (isset($_POST['prediksi'])) {
    $produk_id = $_POST['produk_id'];
    $bulan_prediksi = $_POST['bulan'];
    $tahun_prediksi = $_POST['tahun'];
    
    // Check if user wants to find the best parameters
    $find_best_param = isset($_POST['find_best_param']) ? true : false;
    $selected_alpha = isset($_POST['alpha']) ? (float)$_POST['alpha'] : 0.5;
    $selected_beta = isset($_POST['beta']) ? (float)$_POST['beta'] : 0.5;
    
    // Verify that the selected month/year is valid for prediction
    if ($latest_period) {
        $next_period = getNextPeriod($latest_period['BULAN'], $latest_period['TAHUN']);
        
        if ($bulan_prediksi != $next_period['bulan'] || $tahun_prediksi != $next_period['tahun']) {
            $message = "Hanya boleh memprediksi untuk bulan berikutnya dari data terakhir.";
            $status = "warning";
        } else {
            // Get product name
            $query_produk_name = "SELECT NAMAPRODUK FROM produk WHERE IDPRODUK = '$produk_id'";
            $result_produk_name = mysqli_query($conn, $query_produk_name);
            $produk_name = mysqli_fetch_assoc($result_produk_name)['NAMAPRODUK'];
            
            // Get sales data
            $query_sales = "SELECT BULAN, TAHUN, SUM(JUMLAHJUAL) as TOTAL_JUAL 
                            FROM penjualan 
                            WHERE IDPRODUK = '$produk_id' 
                            GROUP BY TAHUN, BULAN 
                            ORDER BY TAHUN, BULAN";
            $result_sales = mysqli_query($conn, $query_sales);
            
            if (!$result_sales) {
                $message = "Error dalam mengambil data penjualan: " . mysqli_error($conn);
                $status = "danger";
            } else {
                if (mysqli_num_rows($result_sales) < 3) {
                    $message = "Data penjualan tidak cukup untuk Double Exponential Smoothing. Minimal diperlukan 3 bulan data.";
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
                        
                        // Loop mencari kombinasi Alpha dan Beta terbaik
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
                        
                        $message = "Prediksi penjualan untuk " . $produk_name . " pada bulan " . $bulan_nama[$bulan_prediksi] . " " . $tahun_prediksi . 
                                   " adalah " . number_format($best_result['next_forecast'], 2) . " unit dengan Alpha terbaik " . $best_alpha . ", Beta " . $best_beta .
                                   " dan akurasi " . number_format($best_result['accuracy'], 2) . "%";
                    } else {
                        // Use selected parameters
                        $result = calculateDES($sales_data, $selected_alpha, $selected_beta);
                        $result_to_use = $result;
                        $alpha_to_use = $selected_alpha;
                        $beta_to_use = $selected_beta;
                        
                        $message = "Prediksi penjualan untuk " . $produk_name . " pada bulan " . $bulan_nama[$bulan_prediksi] . " " . $tahun_prediksi . 
                                   " adalah " . number_format($result['next_forecast'], 2) . " unit dengan Alpha " . $selected_alpha . ", Beta " . $selected_beta .
                                   " dan akurasi " . number_format($result['accuracy'], 2) . "%";
                    }
                    
                    $status = "success";
                    $periode_prediksi = $bulan_prediksi . "-" . $tahun_prediksi;
                    
                    // Store data in session for save button
                    $_SESSION['prediction_data'] = [
                        'produk_id' => $produk_id,
                        'periode_prediksi' => $periode_prediksi,
                        'next_forecast' => $result_to_use['next_forecast'],
                        'alpha' => $alpha_to_use,
                        'beta' => $beta_to_use, // Parameter Beta
                        'mad' => $result_to_use['mad'],
                        'mse' => $result_to_use['mse'],
                        'mape' => $result_to_use['mape'],
                        'accuracy' => $result_to_use['accuracy']
                    ];
                }
            }
        }
    }
}

// Process save request
if (isset($_POST['simpan_prediksi']) && isset($_SESSION['prediction_data'])) {
    $data = $_SESSION['prediction_data'];
    
    // Pastikan database tabel 'pred' sudah memiliki kolom BETA. 
    $query_insert = "INSERT INTO pred (WAKTUPRED, HASILPRED, PREDIKSIBULANTAHUN, NILAI_PREDIKSI, ALPHA, BETA, MAD, MSE, MAPE, AKURASI) 
                    VALUES (NOW(), '{$data['produk_id']}', '{$data['periode_prediksi']}', {$data['next_forecast']}, 
                    {$data['alpha']}, {$data['beta']}, {$data['mad']}, {$data['mse']}, {$data['mape']}, {$data['accuracy']})";
    
    $result_insert = mysqli_query($conn, $query_insert);
    
    if ($result_insert) {
        $message = "Hasil prediksi berhasil disimpan ke database!";
        $status = "success";
        unset($_SESSION['prediction_data']);
    } else {
        $message = "Error dalam menyimpan hasil prediksi: " . mysqli_error($conn) . "<br><em>Catatan: Pastikan Anda sudah menambahkan kolom BETA pada tabel database.</em>";
        $status = "danger";
    }
}

// PANGGIL HEADER
include "header.php"; 
?>

<style>
    .table-scrollable { max-height: 500px; overflow-y: auto; }
    .best-alpha { background-color: #d4edda; font-weight: bold; }
</style>

<div class="container mt-4 mb-5">
    <div class="row">
        <div class="col-lg-8">
            <div class="card shadow">
                <div class="card-body">
                    <h5 class="card-title">Prediksi Penjualan (Double Exponential Smoothing - Holt)</h5>
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
                            <i class="fas fa-check"></i> Cek Periode Tersedia
                        </button>

                        <?php if ($latest_period && $next_period): ?>
                            <div class="alert alert-info">
                                Data terakhir: <?= $bulan_nama[$latest_period['BULAN']] ?> <?= $latest_period['TAHUN'] ?>. <br>
                                Anda dapat memprediksi untuk: <strong><?= $bulan_nama[$next_period['bulan']] ?> <?= $next_period['tahun'] ?></strong>
                            </div>

                            <input type="hidden" name="bulan" value="<?= $next_period['bulan'] ?>">
                            <input type="hidden" name="tahun" value="<?= $next_period['tahun'] ?>">

                            <div class="mb-3">
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" id="find_best_param" name="find_best_param">
                                    <label class="form-check-label" for="find_best_param">Cari Kombinasi Alpha & Beta Terbaik Otomatis</label>
                                </div>
                            </div>

                            <div class="row" id="parameter_selection">
                                <div class="col-md-6 mb-3">
                                    <label for="alpha" class="form-label">Alpha (Level)</label>
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
                                <i class="fas fa-chart-line"></i> Prediksi
                            </button>
                        <?php endif; ?>
                        
                        <a href="riwayat_prediksi.php" class="btn btn-info ms-2">
                            <i class="fas fa-history"></i> Riwayat Prediksi
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
                    <h5 class="card-title text-primary">Double Exponential Smoothing (Holt)</h5>
                    <p>Rumus Level (L):</p>
                    <pre class="bg-light p-2 border rounded">Lt = α*Yt + (1-α)*(L_t-1 + T_t-1)</pre>
                    <p>Rumus Trend (T):</p>
                    <pre class="bg-light p-2 border rounded">Tt = β*(Lt - L_t-1) + (1-β)*T_t-1</pre>
                    <p>Prediksi (F):</p>
                    <pre class="bg-light p-2 border rounded">F_t+1 = Lt + Tt</pre>
                    <small>
                        * α (Alpha) = parameter pemulusan level<br>
                        * β (Beta) = parameter pemulusan trend
                    </small>
                </div>
            </div>
            
            <div class="card shadow">
                <div class="card-body">
                    <h5 class="card-title text-info">Metrik Akurasi</h5>
                    <ul class="mb-0 ps-3 text-sm">
                        <li><strong>MAD</strong> (Mean Absolute Deviation)</li>
                        <li><strong>MSE</strong> (Mean Squared Error)</li>
                        <li><strong>MAPE</strong> (Mean Absolute Percentage Error)</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
    
    <?php if (isset($result_to_use)): ?>
    <div class="row mt-4">
        <div class="col-12">
            <div class="card shadow">
                <div class="card-body">
                    <h5 class="card-title">Tabel Perhitungan (Alpha = <?= $alpha_to_use ?>, Beta = <?= $beta_to_use ?>)</h5>
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
                                    <th>MAD</th>
                                    <th>MSE</th>
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
                                    <td><?= number_format($result_to_use['errors'][$i]['abs_error'], 2) ?></td>
                                    <td><?= number_format($result_to_use['errors'][$i]['squared_error'], 2) ?></td>
                                    <td><?= number_format($result_to_use['errors'][$i]['percentage_error'], 2) ?></td>
                                </tr>
                                <?php endfor; ?>
                            </tbody>
                            <tfoot class="table-secondary font-weight-bold">
                                <tr>
                                    <th colspan="7" class="text-end">Rata-rata Akurasi:</th>
                                    <th><?= number_format($result_to_use['mad'], 2) ?></th>
                                    <th><?= number_format($result_to_use['mse'], 2) ?></th>
                                    <th><?= number_format($result_to_use['mape'], 2) ?>%</th>
                                </tr>
                                <tr class="table-success">
                                    <th colspan="5" class="text-start">Prediksi Mendatang: <?= $bulan_nama[$bulan_prediksi] . " " . $tahun_prediksi ?></th>
                                    <th colspan="5" class="text-center h5 mb-0 text-success"><?= number_format($result_to_use['next_forecast'], 2) ?> Unit</th>
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

</div> </div> </div> <script src="../assets/js/jquery.min.js"></script>
<script src="../assets/js/bootstrap.bundle.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
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
});
</script>
</body>
</html>