<?php
require_once '../public/include/session.php';

// Require admin login
requireAdmin();

// Get basic statistics
$documentCounts = countDocumentsByType($conn);
$userCount = countUsers($conn);
$flaggedCount = countFlaggedDocuments($conn);

// Enhanced statistics for charts
// 1. Document Categories
$categoryStats = [];
$sql = "SELECT 
    COALESCE(c.CategoryName, 'Uncategorized') as CategoryName,
    COUNT(d.DocumentID) as DocumentCount
FROM document d 
LEFT JOIN category c ON d.CategoryID = c.CategoryID 
WHERE d.IsDeleted = 0 
GROUP BY d.CategoryID, c.CategoryName
ORDER BY DocumentCount DESC";
$result = mysqli_query($conn, $sql);
if ($result) {
    while ($row = mysqli_fetch_assoc($result)) {
        $categoryStats[$row['CategoryName']] = $row['DocumentCount'];
    }
}

// 2. Access Level Distribution
$accessLevelStats = [];
$sql = "SELECT 
    CONCAT('Level ', a.AccessLevelID, ' - ', a.LevelName) as LevelName,
    COUNT(d.DocumentID) as DocumentCount
FROM document d 
JOIN accesslevel a ON d.AccessLevel = a.AccessLevelID 
WHERE d.IsDeleted = 0 
GROUP BY d.AccessLevel, a.LevelName, a.AccessLevelID
ORDER BY a.AccessLevelID";
$result = mysqli_query($conn, $sql);
if ($result) {
    while ($row = mysqli_fetch_assoc($result)) {
        $accessLevelStats[$row['LevelName']] = $row['DocumentCount'];
    }
}

// 3. Monthly Upload Trends (last 6 months)
$monthlyStats = [];
$sql = "SELECT 
    DATE_FORMAT(UploadDate, '%M %Y') as MonthName,
    DATE_FORMAT(UploadDate, '%Y-%m') as MonthKey,
    SUM(CASE WHEN FileType = 'pdf' THEN 1 ELSE 0 END) as PDFCount,
    SUM(CASE WHEN FileType IN ('jpg', 'jpeg', 'png', 'gif') THEN 1 ELSE 0 END) as ImageCount,
    COUNT(DocumentID) as TotalCount
FROM document 
WHERE IsDeleted = 0 
AND UploadDate >= DATE_SUB(NOW(), INTERVAL 6 MONTH)
GROUP BY DATE_FORMAT(UploadDate, '%Y-%m')
ORDER BY MonthKey";
$result = mysqli_query($conn, $sql);
if ($result) {
    while ($row = mysqli_fetch_assoc($result)) {
        $monthlyStats[$row['MonthName']] = [
            'pdf' => $row['PDFCount'],
            'image' => $row['ImageCount'],
            'total' => $row['TotalCount']
        ];
    }
}

// If no data exists, create sample structure
if (empty($categoryStats)) {
    $categoryStats = ['No Documents' => 0];
}
if (empty($accessLevelStats)) {
    $accessLevelStats = ['No Access Levels' => 0];
}
if (empty($monthlyStats)) {
    $monthlyStats = [date('F Y') => ['pdf' => 0, 'image' => 0, 'total' => 0]];
}

include 'include/header.php';
include 'include/admin-sidebar.php';
?>

<!-- Main Panel -->
<div class="main-panel">
  <div class="content-wrapper">
    <!-- Welcome Message -->
    <div class="row">
      <div class="col-md-12 grid-margin">
        <div class="row">
          <div class="col-12 col-xl-8 mb-4 mb-xl-0">
            <h3 class="font-weight-bold">Administrator Dashboard</h3>
            <h6 class="font-weight-normal mb-0">Manage your document archive directory</h6>
          </div>
        </div>
      </div>
    </div>
    
    <!-- Quick Stats -->
    <div class="row">
      <div class="col-md-3 grid-margin stretch-card">
        <div class="card card-tale">
          <div class="card-body">
            <p class="mb-4">Total Documents</p>
            <p class="fs-30 mb-2"><?php echo $documentCounts['total']; ?></p>
            <p>Across all users</p>
          </div>
        </div>
      </div>
      <div class="col-md-3 grid-margin stretch-card">
        <div class="card card-dark-blue">
          <div class="card-body">
            <p class="mb-4">Total Users</p>
            <p class="fs-30 mb-2"><?php echo $userCount; ?></p>
          </div>
        </div>
      </div>
      <div class="col-md-3 grid-margin stretch-card">
        <div class="card card-light-blue">
          <div class="card-body">
            <p class="mb-4">Flagged Documents</p>
            <p class="fs-30 mb-2"><?php echo $flaggedCount; ?></p>
          </div>
        </div>
      </div>
      <div class="col-md-3 grid-margin stretch-card">
        <div class="card card-light-danger">
          <div class="card-body">
            <p class="mb-4">Document Types</p>
            <div class="d-flex justify-content-between">
              <p>PDF: <span class="font-weight-bold"><?php echo $documentCounts['pdf']; ?></span></p>
              <p>PNG: <span class="font-weight-bold"><?php echo $documentCounts['png']; ?></span></p>
              <p>JPG: <span class="font-weight-bold"><?php echo $documentCounts['jpg']; ?></span></p>
            </div>
          </div>
        </div>
      </div>
    </div>
    
    <!-- Charts Row -->
    <div class="row">
      <!-- Document Categories Chart -->
      <div class="col-md-6 grid-margin stretch-card">
        <div class="card">
          <div class="card-body">
            <h4 class="card-title">Document Categories</h4>
            <div class="chart-container">
              <canvas id="documentCategoriesChart" style="height: 300px;"></canvas>
            </div>
            <div class="mt-3">
              <?php foreach ($categoryStats as $category => $count): ?>
                <div class="d-flex justify-content-between align-items-center mb-2">
                  <span><?php echo htmlspecialchars($category); ?></span>
                  <span class="badge badge-primary"><?php echo $count; ?></span>
                </div>
              <?php endforeach; ?>
            </div>
          </div>
        </div>
      </div>
      
      <!-- Access Level Distribution Chart -->
      <div class="col-md-6 grid-margin stretch-card">
        <div class="card">
          <div class="card-body">
            <h4 class="card-title">Access Level Distribution</h4>
            <div class="chart-container">
              <canvas id="accessLevelChart" style="height: 300px;"></canvas>
            </div>
            <div class="mt-3">
              <?php foreach ($accessLevelStats as $level => $count): ?>
                <div class="d-flex justify-content-between align-items-center mb-2">
                  <span><?php echo htmlspecialchars($level); ?></span>
                  <span class="badge badge-info"><?php echo $count; ?></span>
                </div>
              <?php endforeach; ?>
            </div>
          </div>
        </div>
      </div>
    </div>
    
    <!-- File Upload Trends Chart -->
    <div class="row">
      <div class="col-md-12 grid-margin stretch-card">
        <div class="card">
          <div class="card-body">
            <h4 class="card-title">File Upload Trends</h4>
            <p class="card-description">Monthly upload statistics for the last 6 months</p>
            <div class="chart-container">
              <canvas id="fileUploadTrendsChart" style="height: 400px;"></canvas>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>

<!-- Chart.js Scripts -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Document Categories chart
    var categoryCtx = document.getElementById('documentCategoriesChart').getContext('2d');
    var categoryChart = new Chart(categoryCtx, {
        type: 'doughnut',
        data: {
            labels: [<?php echo "'" . implode("', '", array_keys($categoryStats)) . "'"; ?>],
            datasets: [{
                data: [<?php echo implode(', ', array_values($categoryStats)); ?>],
                backgroundColor: [
                    '#4B49AC', '#248AFD', '#57B657', '#FFC100', 
                    '#FF4747', '#6610f2', '#e83e8c', '#fd7e14',
                    '#20c997', '#6f42c1'
                ],
                borderWidth: 2,
                borderColor: '#fff'
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'bottom',
                    labels: {
                        padding: 20,
                        usePointStyle: true
                    }
                }
            }
        }
    });

    // Access Level Distribution chart
    var accessLevelCtx = document.getElementById('accessLevelChart').getContext('2d');
    var accessLevelChart = new Chart(accessLevelCtx, {
        type: 'bar',
        data: {
            labels: [<?php echo "'" . implode("', '", array_keys($accessLevelStats)) . "'"; ?>],
            datasets: [{
                label: 'Documents',
                data: [<?php echo implode(', ', array_values($accessLevelStats)); ?>],
                backgroundColor: '#4B49AC',
                borderColor: '#4B49AC',
                borderWidth: 1,
                borderRadius: 4
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: false
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        stepSize: 1
                    }
                }
            }
        }
    });

    // File Upload Trends chart
    var trendsCtx = document.getElementById('fileUploadTrendsChart').getContext('2d');
    var trendsChart = new Chart(trendsCtx, {
        type: 'line',
        data: {
            labels: [<?php echo "'" . implode("', '", array_keys($monthlyStats)) . "'"; ?>],
            datasets: [
                {
                    label: 'Total Uploads',
                    data: [<?php 
                        $totals = [];
                        foreach($monthlyStats as $month => $data) {
                            $totals[] = $data['total'];
                        }
                        echo implode(', ', $totals);
                    ?>],
                    borderColor: '#4B49AC',
                    backgroundColor: 'rgba(75, 73, 172, 0.1)',
                    borderWidth: 3,
                    fill: true,
                    tension: 0.4
                },
                {
                    label: 'PDF Files',
                    data: [<?php 
                        $pdfs = [];
                        foreach($monthlyStats as $month => $data) {
                            $pdfs[] = $data['pdf'];
                        }
                        echo implode(', ', $pdfs);
                    ?>],
                    borderColor: '#FFC100',
                    backgroundColor: 'rgba(255, 193, 0, 0.1)',
                    borderWidth: 2,
                    fill: false,
                    tension: 0.4
                },
                {
                    label: 'Image Files',
                    data: [<?php 
                        $images = [];
                        foreach($monthlyStats as $month => $data) {
                            $images[] = $data['image'];
                        }
                        echo implode(', ', $images);
                    ?>],
                    borderColor: '#57B657',
                    backgroundColor: 'rgba(87, 182, 87, 0.1)',
                    borderWidth: 2,
                    fill: false,
                    tension: 0.4
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'top',
                    labels: {
                        padding: 20,
                        usePointStyle: true
                    }
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        stepSize: 1
                    }
                }
            },
            interaction: {
                intersect: false,
                mode: 'index'
            }
        }
    });
});
</script>

<?php
include 'include/footer.php';
?>
