<?php
require_once 'app/functions/MY_model.php';

$dokter = mysqli_query($conn, "SELECT * FROM dokter");
$count_dokter = mysqli_num_rows($dokter);

$pasien = mysqli_query($conn, "SELECT * FROM pasien");
$count_pasien = mysqli_num_rows($pasien);

$ruang = mysqli_query($conn, "SELECT * FROM ruang");
$count_ruang = mysqli_num_rows($ruang);

$obat = mysqli_query($conn, "SELECT * FROM obat");
$count_obat = mysqli_num_rows($obat);

// $_SESSION['title'] = 'Dashboard';
?>
<?php
require_once 'app/functions/MY_model.php';

$dokter = mysqli_query($conn, "SELECT * FROM dokter");
$count_dokter = mysqli_num_rows($dokter);

$pasien = mysqli_query($conn, "SELECT * FROM pasien");
$count_pasien = mysqli_num_rows($pasien);

$ruang = mysqli_query($conn, "SELECT * FROM ruang");
$count_ruang = mysqli_num_rows($ruang);

$obat = mysqli_query($conn, "SELECT * FROM obat");
$count_obat = mysqli_num_rows($obat);

// Check if v2.0 features are available
$appointmentTableExists = mysqli_num_rows(mysqli_query($conn, "SHOW TABLES LIKE 'appointments'")) > 0;
$queueTableExists = mysqli_num_rows(mysqli_query($conn, "SHOW TABLES LIKE 'queue_status'")) > 0;
$v2Available = $appointmentTableExists && $queueTableExists;

// $_SESSION['title'] = 'Dashboard';
?>

<?php if (!$v2Available): ?>
<!-- Upgrade Notification -->
<div class="row">
  <div class="col-12">
    <div class="alert alert-info alert-dismissible fade show" role="alert">
      <h4 class="alert-heading">🚀 Smart Clinic v2.0 Available!</h4>
      <p class="mb-0">
        Upgrade sistem Anda dengan fitur-fitur canggih: Smart Appointment System, Queue Management, Enhanced Medical Records, dan Financial Module.
        <a href="setup_v2.php" class="btn btn-primary btn-sm ml-2">
          <i class="feather icon-arrow-up"></i> Upgrade Now
        </a>
      </p>
      <button type="button" class="close" data-dismiss="alert" aria-label="Close">
        <span aria-hidden="true">&times;</span>
      </button>
    </div>
  </div>
</div>
<?php endif; ?>

<!-- Dashboard Analytics Start -->
<section id="dashboard-analytics">
  <div class="row">
    <div class="col-lg-3 col-md-6 col-12">
      <div class="card">
        <div class="card-header d-flex flex-column align-items-start pb-0">
          <div class="avatar bg-rgba-primary p-50 m-0">
            <div class="avatar-content">
              <i class="feather icon-users text-primary font-medium-5"></i>
            </div>
          </div>
          <h2 class="text-bold-700 mt-1 mb-25"><?= $count_dokter; ?></h2>
          <p class="mb-0">Dokter</p>
        </div>
        <div class="card-content">
          <!-- <div id="dokter-chart"></div> -->
        </div>
      </div>
    </div>

    <div class="col-lg-3 col-md-6 col-12">
      <div class="card">
        <div class="card-header d-flex flex-column align-items-start pb-0">
          <div class="avatar bg-rgba-primary p-50 m-0">
            <div class="avatar-content">
              <i class="feather icon-users text-primary font-medium-5"></i>
            </div>
          </div>
          <h2 class="text-bold-700 mt-1 mb-25"><?= $count_pasien; ?></h2>
          <p class="mb-0">Pasien</p>
        </div>
        <div class="card-content">
          <!-- <div id="pasien-chart"></div> -->
        </div>
      </div>
    </div>

    <div class="col-lg-3 col-md-6 col-12">
      <div class="card">
        <div class="card-header d-flex flex-column align-items-start pb-0">
          <div class="avatar bg-rgba-primary p-50 m-0">
            <div class="avatar-content">
              <i class="feather icon-users text-primary font-medium-5"></i>
            </div>
          </div>
          <h2 class="text-bold-700 mt-1 mb-25"><?= $count_ruang; ?></h2>
          <p class="mb-0">Ruang</p>
        </div>
        <div class="card-content">
          <!-- <div id="ruang-chart"></div> -->
        </div>
      </div>
    </div>

    <div class="col-lg-3 col-md-6 col-12">
      <div class="card">
        <div class="card-header d-flex flex-column align-items-start pb-0">
          <div class="avatar bg-rgba-warning p-50 m-0">
            <div class="avatar-content">
              <i class="feather icon-package text-warning font-medium-5"></i>
            </div>
          </div>
          <h2 class="text-bold-700 mt-1 mb-25"><?= $count_obat; ?></h2>
          <p class="mb-0">Obat</p>
        </div>
        <div class="card-content">
          <!-- <div id="obat-chart"></div> -->
        </div>
      </div>
    </div>
  </div>

  <?php if ($v2Available): ?>
  <!-- v2.0 Features Dashboard -->
  <div class="row">
    <div class="col-12">
      <div class="card">
        <div class="card-header">
          <h4 class="card-title">🚀 Smart Clinic v2.0 Features</h4>
        </div>
        <div class="card-content">
          <div class="card-body">
            <div class="row">
              <div class="col-md-3 col-sm-6">
                <div class="text-center">
                  <a href="?page=appointment" class="text-decoration-none">
                    <div class="avatar bg-rgba-info p-50 mx-auto mb-2">
                      <div class="avatar-content">
                        <i class="feather icon-calendar text-info font-large-1"></i>
                      </div>
                    </div>
                    <h5>Appointment System</h5>
                    <p class="text-muted">Smart scheduling & queue management</p>
                  </a>
                </div>
              </div>
              <div class="col-md-3 col-sm-6">
                <div class="text-center">
                  <a href="?page=queue-management" class="text-decoration-none">
                    <div class="avatar bg-rgba-warning p-50 mx-auto mb-2">
                      <div class="avatar-content">
                        <i class="feather icon-users text-warning font-large-1"></i>
                      </div>
                    </div>
                    <h5>Queue Management</h5>
                    <p class="text-muted">Real-time patient queue tracking</p>
                  </a>
                </div>
              </div>
              <div class="col-md-3 col-sm-6">
                <div class="text-center">
                  <div class="avatar bg-rgba-success p-50 mx-auto mb-2">
                    <div class="avatar-content">
                      <i class="feather icon-heart text-success font-large-1"></i>
                    </div>
                  </div>
                  <h5>Enhanced Records</h5>
                  <p class="text-muted">Comprehensive medical records</p>
                </div>
              </div>
              <div class="col-md-3 col-sm-6">
                <div class="text-center">
                  <div class="avatar bg-rgba-primary p-50 mx-auto mb-2">
                    <div class="avatar-content">
                      <i class="feather icon-dollar-sign text-primary font-large-1"></i>
                    </div>
                  </div>
                  <h5>Financial Module</h5>
                  <p class="text-muted">Billing & payment management</p>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
  <?php endif; ?>
</section>
<!-- Dashboard Analytics end -->
<script>
  var count_dokter = '<?php echo $count_dokter; ?>';
</script>
<?php
$addon_script = ['assets/vendors/js/charts/apexcharts.min.js', 'assets/js/pages/dashboard.js'];
$prepend_style = ['assets/css/pages/dashboard.css'];
?>