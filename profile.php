<?php
ob_start();
require_once 'auth.php';
require_once 'profile_functions.php';
include 'header.php';

try {
    $profile = new ProfileFunctions();
    
    // Handle form submissions
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        if (isset($_POST['update_personal_info'])) {
            $data = [
                'first_name' => $_POST['first_name'],
                'last_name' => $_POST['last_name'],
                'second_name' => $_POST['second_name'] ?? '',
                'phone' => $_POST['phone'],
                'kra_pin' => $_POST['kra_pin']
            ];
            $profile->updatePersonalInfo($data);
            $success = "Personal information updated successfully!";
        }
        
        if (isset($_POST['change_password'])) {
            $profile->changePassword($_POST['current_password'], $_POST['new_password']);
            $success = "Password updated successfully!";
        }
        
        if (isset($_POST['toggle_two_factor'])) {
            $enable = $_POST['two_factor_enabled'] === '1';
            $profile->toggleTwoFactor($enable);
            $success = $enable ? "Two-factor authentication enabled!" : "Two-factor authentication disabled!";
        }
    }
    
    // Handle avatar upload
    if (isset($_FILES['avatar'])) {
        try {
            $avatarPath = $profile->updateAvatar($_FILES['avatar']);
            $success = "Profile picture updated successfully!";
        } catch (Exception $e) {
            $error = $e->getMessage();
        }
    }
    
    // Get user data
    $userData = $profile->getUserData();
    
} catch (Exception $e) {
    $error = $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Profile</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        :root {
            --primary-color: #d9232e;
            --secondary-color: #151515;
            --light-color: #ffffff;
            --grey-color: #6c757d;
            --dark-grey: #343a40;
            --border-radius: 0.375rem;
            --box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
            --transition: all 0.3s ease;
        }
        
        body {
            background-color: #f8f9fa;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        .profile-card {
            border-radius: var(--border-radius);
            box-shadow: var(--box-shadow);
            overflow: hidden;
            transition: var(--transition);
        }
        
        .profile-card:hover {
            box-shadow: 0 1rem 1.5rem rgba(0, 0, 0, 0.1);
        }
        
        .card-header {
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            color: var(--light-color);
            border-bottom: none;
        }
        
        .profile-avatar {
            position: relative;
            width: 120px;
            height: 120px;
            margin: 0 auto;
        }
        
        .profile-avatar img {
            border: 3px solid rgba(255, 255, 255, 0.3);
            transition: var(--transition);
        }
        
        .profile-avatar:hover img {
            transform: scale(1.05);
            border-color: rgba(255, 255, 255, 0.6);
        }
        
        .avatar-edit {
            position: absolute;
            bottom: 5px;
            right: 5px;
            width: 36px;
            height: 36px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            background-color: var(--primary-color);
            color: white;
            border: 2px solid white;
            opacity: 0;
            transform: translateY(10px);
            transition: var(--transition);
        }
        
        .profile-avatar:hover .avatar-edit {
            opacity: 1;
            transform: translateY(0);
        }
        
        .nav-tabs .nav-link {
            color: var(--grey-color);
            font-weight: 500;
            border: none;
            position: relative;
        }
        
        .nav-tabs .nav-link.active {
            color: var(--primary-color);
            background: transparent;
        }
        
        .nav-tabs .nav-link.active::after {
            content: "";
            position: absolute;
            bottom: -1px;
            left: 0;
            width: 100%;
            height: 2px;
            background-color: var(--primary-color);
        }
        
        .form-control:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 0.25rem rgba(217, 35, 46, 0.15);
        }
        
        .btn-primary {
            background-color: var(--primary-color);
            border-color: var(--primary-color);
        }
        
        .btn-primary:hover {
            background-color: #b51d27;
            border-color: #a81a22;
        }
        
        .activity-item {
            transition: var(--transition);
        }
        
        .activity-item:hover {
            background-color: rgba(0, 0, 0, 0.02);
        }
        
        @media (max-width: 768px) {
            .profile-avatar {
                width: 100px;
                height: 100px;
            }
        }
    </style>
</head>
<body>
<div class="container py-4">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card profile-card mb-4">
                <div class="card-header text-center py-4">
                    <div class="profile-avatar mb-3">
                        <img src="<?= htmlspecialchars($userData['avatar_path'] ?? 'assets/images/default-avatar.png') ?>" 
                             class="rounded-circle shadow" 
                             style="width: 120px; height: 120px; object-fit: cover;">
                        <button class="btn btn-sm avatar-edit" data-bs-toggle="modal" data-bs-target="#avatarModal">
                            <i class="fas fa-camera"></i>
                        </button>
                    </div>
                    <h3 class="mb-1"><?= htmlspecialchars($userData['surname'] ?? '') ?> <?= htmlspecialchars($userData['first_name'] ?? '') ?></h3>
                    <p class="mb-0"><?= htmlspecialchars($userData['email'] ?? '') ?></p>
                </div>
                
                <div class="card-body">
                    <?php if (isset($success)): ?>
                        <div class="alert alert-success alert-dismissible fade show">
                            <i class="fas fa-check-circle me-2"></i>
                            <?= htmlspecialchars($success) ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    <?php endif; ?>
                    
                    <?php if (isset($error)): ?>
                        <div class="alert alert-danger alert-dismissible fade show">
                            <i class="fas fa-exclamation-circle me-2"></i>
                            <?= htmlspecialchars($error) ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    <?php endif; ?>
                    
                    <ul class="nav nav-tabs mb-4" id="profileTabs" role="tablist">
                        <li class="nav-item" role="presentation">
                            <button class="nav-link active" id="personal-tab" data-bs-toggle="tab" data-bs-target="#personal" type="button" role="tab">
                                <i class="fas fa-user me-2"></i> Personal Info
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="security-tab" data-bs-toggle="tab" data-bs-target="#security" type="button" role="tab">
                                <i class="fas fa-lock me-2"></i> Security
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="activity-tab" data-bs-toggle="tab" data-bs-target="#activity" type="button" role="tab">
                                <i class="fas fa-history me-2"></i> Activity
                            </button>
                        </li>
                    </ul>
                    
                    <div class="tab-content" id="profileTabsContent">
                        <!-- Personal Info Tab -->
                        <div class="tab-pane fade show active" id="personal" role="tabpanel">
                            <form id="personalInfoForm" method="POST">
                                <input type="hidden" name="update_personal_info" value="1">
                                <div class="row mb-3">
                                    <div class="col-md-4 mb-3">
                                        <label class="form-label">Surname</label>
                                        <input type="text" class="form-control" name="last_name" 
                                               value="<?= htmlspecialchars($userData['surname'] ?? '') ?>" required>
                                    </div>
                                    <div class="col-md-4 mb-3">
                                        <label class="form-label">First Name</label>
                                        <input type="text" class="form-control" name="first_name" 
                                               value="<?= htmlspecialchars($userData['first_name'] ?? '') ?>" required>
                                    </div>
                                    <div class="col-md-4 mb-3">
                                        <label class="form-label">Second Name</label>
                                        <input type="text" class="form-control" name="second_name" 
                                               value="<?= htmlspecialchars($userData['second_name'] ?? '') ?>">
                                    </div>
                                </div>
                                
                                <div class="mb-3">
                                    <label class="form-label">Email</label>
                                    <input type="email" class="form-control" value="<?= htmlspecialchars($userData['email'] ?? '') ?>" readonly>
                                </div>
                                
                                <div class="row mb-3">
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Phone Number</label>
                                        <input type="tel" class="form-control" name="phone" 
                                               value="<?= htmlspecialchars($userData['phone'] ?? '') ?>">
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">KRA PIN</label>
                                        <input type="text" class="form-control" name="kra_pin" 
                                               value="<?= htmlspecialchars($userData['kra_pin'] ?? '') ?>">
                                    </div>
                                </div>
                                
                                <div class="text-end mt-4">
                                    <button type="submit" class="btn btn-primary px-4">
                                        <i class="fas fa-save me-2"></i> Save Changes
                                    </button>
                                </div>
                            </form>
                        </div>
                        
                        <!-- Security Tab -->
                        <div class="tab-pane fade" id="security" role="tabpanel">
                            <div class="mb-4">
                                <h5 class="mb-3"><i class="fas fa-key me-2"></i> Change Password</h5>
                                <form id="passwordForm" method="POST">
                                    <input type="hidden" name="change_password" value="1">
                                    <div class="mb-3">
                                        <label class="form-label">Current Password</label>
                                        <input type="password" class="form-control" name="current_password" required>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label class="form-label">New Password</label>
                                        <input type="password" class="form-control" name="new_password" required>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label class="form-label">Confirm New Password</label>
                                        <input type="password" class="form-control" name="confirm_password" required>
                                    </div>
                                    
                                    <div class="text-end">
                                        <button type="submit" class="btn btn-primary px-4">
                                            <i class="fas fa-save me-2"></i> Update Password
                                        </button>
                                    </div>
                                </form>
                            </div>
                            
                            <div class="border-top pt-4">
                                <h5 class="mb-3"><i class="fas fa-shield-alt me-2"></i> Two-Factor Authentication</h5>
                                <form method="POST">
                                    <input type="hidden" name="toggle_two_factor" value="1">
                                    <div class="d-flex justify-content-between align-items-center p-3 bg-light rounded">
                                        <div>
                                            <h6 class="mb-1">SMS Authentication</h6>
                                            <p class="text-muted small mb-0">Add an extra layer of security to your account</p>
                                        </div>
                                        <div class="form-check form-switch">
                                            <input class="form-check-input" type="checkbox" name="two_factor_enabled" 
                                                   id="twoFactorToggle" <?= ($userData['two_factor_enabled'] ?? false) ? 'checked' : '' ?> value="1">
                                            <label class="form-check-label" for="twoFactorToggle"></label>
                                        </div>
                                    </div>
                                    <div class="text-end mt-3">
                                        <button type="submit" class="btn btn-primary px-4">
                                            <i class="fas fa-save me-2"></i> Save Settings
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>
                        
                        <!-- Activity Tab -->
                        <div class="tab-pane fade" id="activity" role="tabpanel">
                            <h5 class="mb-3"><i class="fas fa-history me-2"></i> Recent Activity</h5>
                            <div class="list-group">
                                <?php if (!empty($userData['activity'])): ?>
                                    <?php foreach ($userData['activity'] as $activity): ?>
                                        <div class="list-group-item border-0 px-0 py-3 activity-item">
                                            <div class="d-flex align-items-center">
                                                <div class="flex-shrink-0 bg-light rounded-circle p-2 me-3">
                                                    <i class="fas <?= htmlspecialchars($activity['icon']) ?> text-primary"></i>
                                                </div>
                                                <div class="flex-grow-1">
                                                    <h6 class="mb-1"><?= htmlspecialchars($activity['title']) ?></h6>
                                                    <p class="small text-muted mb-0"><?= htmlspecialchars($activity['description']) ?></p>
                                                </div>
                                                <small class="text-muted"><?= htmlspecialchars($activity['time']) ?></small>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <div class="text-center py-4">
                                        <i class="fas fa-history fa-3x text-muted mb-3"></i>
                                        <p class="text-muted">No recent activity found</p>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Avatar Update Modal -->
<div class="modal fade" id="avatarModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Update Profile Picture</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="avatarForm" method="POST" enctype="multipart/form-data">
                    <div class="mb-3">
                        <label for="avatarUpload" class="form-label">Choose an image (JPG, PNG, GIF - max 2MB)</label>
                        <input class="form-control" type="file" id="avatarUpload" name="avatar" accept="image/*">
                    </div>
                    <div class="text-center mb-3">
                        <img id="avatarPreview" src="<?= htmlspecialchars($userData['avatar_path'] ?? 'assets/images/default-avatar.png') ?>" 
                             class="rounded-circle shadow" 
                             style="width: 150px; height: 150px; object-fit: cover;">
                    </div>
                    <div class="text-end">
                        <button type="button" class="btn btn-outline-secondary me-2" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save me-2"></i> Save Changes
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Avatar preview
    const avatarUpload = document.getElementById('avatarUpload');
    if (avatarUpload) {
        avatarUpload.addEventListener('change', function() {
            if (this.files && this.files[0]) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    document.getElementById('avatarPreview').src = e.target.result;
                }
                reader.readAsDataURL(this.files[0]);
            }
        });
    }
    
    // Form submissions
    const forms = ['personalInfoForm', 'passwordForm', 'avatarForm'];
    forms.forEach(formId => {
        const form = document.getElementById(formId);
        if (form) {
            form.addEventListener('submit', function(e) {
                const submitBtn = this.querySelector('button[type="submit"]');
                if (submitBtn) {
                    submitBtn.disabled = true;
                    submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Processing...';
                }
            });
        }
    });
});
</script>

<?php include 'footer.php'; 
ob_end_flush();
?>