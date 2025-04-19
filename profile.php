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
                'phone' => $_POST['phone'],
                'organization' => $_POST['organization']
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
    <style>
        :root {
            --primary-color: #d9232e; /* KRA Red */
            --secondary-color: #151515; /* Dark Black */
            --light-color: #ffffff; /* White */
            --grey-color: #6c757d; /* Grey */
            --dark-grey: #343a40; /* Dark Grey */
            --border-radius: 0.375rem;
            --box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
            --transition: all 0.3s ease;
            --animation-duration: 0.4s;
        }
        
        /* Smooth page load animation */
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }
        
        .container {
            animation: fadeIn var(--animation-duration) ease-out;
        }
        
        /* Profile card enhancements */
        .profile-card {
            border: none;
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
            position: relative;
            overflow: hidden;
        }
        
        .card-header::after {
            content: "";
            position: absolute;
            top: -50%;
            right: -50%;
            width: 100%;
            height: 200%;
            background: rgba(255, 255, 255, 0.1);
            transform: rotate(30deg);
            pointer-events: none;
        }
        
        /* Avatar styles with animation */
        .profile-avatar {
            position: relative;
            width: 120px;
            height: 120px;
            margin: 0 auto;
            transition: var(--transition);
        }
        
        .profile-avatar img {
            transition: var(--transition);
            border: 3px solid rgba(255, 255, 255, 0.3);
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
        
        /* Tab styles */
        .nav-tabs {
            border-bottom: 1px solid rgba(0, 0, 0, 0.05);
        }
        
        .nav-tabs .nav-link {
            color: var(--grey-color);
            font-weight: 500;
            border: none;
            padding: 0.75rem 1.25rem;
            position: relative;
            transition: var(--transition);
        }
        
        .nav-tabs .nav-link::after {
            content: "";
            position: absolute;
            bottom: -1px;
            left: 0;
            width: 0;
            height: 2px;
            background-color: var(--primary-color);
            transition: var(--transition);
        }
        
        .nav-tabs .nav-link:hover {
            color: var(--primary-color);
        }
        
        .nav-tabs .nav-link.active {
            color: var(--primary-color);
            background: transparent;
        }
        
        .nav-tabs .nav-link.active::after {
            width: 100%;
        }
        
        /* Form input animations */
        .form-control {
            border: 1px solid #e0e0e0;
            transition: var(--transition);
        }
        
        .form-control:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 0.25rem rgba(217, 35, 46, 0.15);
        }
        
        /* Button enhancements */
        .btn-primary {
            background-color: var(--primary-color);
            border-color: var(--primary-color);
            position: relative;
            overflow: hidden;
        }
        
        .btn-primary::after {
            content: "";
            position: absolute;
            top: -50%;
            right: -50%;
            width: 100%;
            height: 200%;
            background: rgba(255, 255, 255, 0.1);
            transform: rotate(30deg) translate(-20px, -20px);
            transition: var(--transition);
        }
        
        .btn-primary:hover::after {
            transform: rotate(30deg) translate(20px, 20px);
        }
        
        /* Activity list animations */
        .activity-item {
            transition: var(--transition);
            opacity: 0;
            transform: translateX(-10px);
            animation: fadeInRight var(--animation-duration) ease-out forwards;
        }
        
        @keyframes fadeInRight {
            to {
                opacity: 1;
                transform: translateX(0);
            }
        }
        
        /* Create staggered animation delays */
        .activity-item:nth-child(1) { animation-delay: 0.1s; }
        .activity-item:nth-child(2) { animation-delay: 0.2s; }
        .activity-item:nth-child(3) { animation-delay: 0.3s; }
        .activity-item:nth-child(4) { animation-delay: 0.4s; }
        
        /* Password strength meter animation */
        .password-strength .progress-bar {
            transition: width 0.5s ease, background-color 0.3s ease;
        }
        
        /* Alert animations */
        .alert {
            animation: slideInDown 0.5s ease-out;
        }
        
        @keyframes slideInDown {
            from {
                transform: translateY(-20px);
                opacity: 0;
            }
            to {
                transform: translateY(0);
                opacity: 1;
            }
        }
        
        /* Modal animations */
        .modal.fade .modal-dialog {
            transform: translateY(-50px);
            transition: var(--transition);
        }
        
        .modal.show .modal-dialog {
            transform: translateY(0);
        }
        
        /* Responsive adjustments */
        @media (max-width: 768px) {
            .profile-avatar {
                width: 100px;
                height: 100px;
            }
            
            .nav-tabs .nav-link {
                padding: 0.5rem 0.75rem;
                font-size: 0.9rem;
            }
        }
    </style>
</head>
<body>
<div class="container mt-4">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card profile-card shadow-sm">
                <div class="card-header bg-white pt-4 pb-2">
                    <div class="text-center">
                        <div class="profile-avatar mx-auto mb-3">
                            <img src="<?= htmlspecialchars($userData['avatar'] ?? 'assets/images/default-avatar.png') ?>" 
                                 alt="Profile" 
                                 class="rounded-circle shadow" 
                                 style="width: 120px; height: 120px; object-fit: cover;">
                            <button class="btn btn-sm avatar-edit" data-bs-toggle="modal" data-bs-target="#avatarModal">
                                <i class="fas fa-camera"></i>
                            </button>
                        </div>
                        <h3 class="card-title text-white fw-bold mb-1"><?= htmlspecialchars($userData['first_name'] ?? '') ?> <?= htmlspecialchars($userData['last_name'] ?? '') ?></h3>
                        <p class="text-white-50">Manage your account settings</p>
                    </div>
                </div>
                
                <div class="card-body px-4 px-md-5 py-4">
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
                                <i class="fas fa-user me-2"></i>Personal Info
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="security-tab" data-bs-toggle="tab" data-bs-target="#security" type="button" role="tab">
                                <i class="fas fa-lock me-2"></i>Security
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="activity-tab" data-bs-toggle="tab" data-bs-target="#activity" type="button" role="tab">
                                <i class="fas fa-history me-2"></i>Activity
                            </button>
                        </li>
                    </ul>
                    
                    <div class="tab-content" id="profileTabsContent">
                        <!-- Personal Info Tab -->
                        <div class="tab-pane fade show active" id="personal" role="tabpanel">
                            <form id="personalInfoForm">
                                <div class="row mb-3">
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">First Name</label>
                                        <input type="text" class="form-control" name="first_name" value="<?= htmlspecialchars($userData['first_name'] ?? '') ?>">
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">Last Name</label>
                                        <input type="text" class="form-control" name="last_name" value="<?= htmlspecialchars($userData['last_name'] ?? '') ?>">
                                    </div>
                                </div>
                                
                                <div class="mb-3">
                                    <label class="form-label">Email</label>
                                    <input type="email" class="form-control" value="<?= htmlspecialchars($userData['email'] ?? '') ?>" readonly>
                                    <small class="text-muted">Contact support to change your email</small>
                                </div>
                                
                                <div class="mb-3">
                                    <label class="form-label">Phone Number</label>
                                    <div class="input-group">
                                        <span class="input-group-text"><i class="fas fa-phone"></i></span>
                                        <input type="tel" class="form-control" name="phone" value="<?= htmlspecialchars($userData['phone'] ?? '') ?>">
                                    </div>
                                </div>
                                
                                <div class="mb-3">
                                    <label class="form-label">Organization</label>
                                    <div class="input-group">
                                        <span class="input-group-text"><i class="fas fa-building"></i></span>
                                        <input type="text" class="form-control" name="organization" value="<?= htmlspecialchars($userData['organization'] ?? '') ?>">
                                    </div>
                                </div>
                                
                                <div class="text-end mt-4">
                                    <button type="submit" class="btn btn-primary px-4">
                                        <span class="submit-text">Save Changes</span>
                                        <span class="spinner-border spinner-border-sm d-none" role="status"></span>
                                    </button>
                                </div>
                            </form>
                        </div>
                        
                        <!-- Security Tab -->
                        <div class="tab-pane fade" id="security" role="tabpanel">
                            <div class="mb-4">
                                <h5 class="fw-bold mb-3"><i class="fas fa-key me-2"></i>Change Password</h5>
                                <form id="passwordForm">
                                    <div class="mb-3">
                                        <label class="form-label">Current Password</label>
                                        <div class="input-group">
                                            <span class="input-group-text"><i class="fas fa-lock"></i></span>
                                            <input type="password" class="form-control" name="current_password" required>
                                        </div>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label class="form-label">New Password</label>
                                        <div class="input-group">
                                            <span class="input-group-text"><i class="fas fa-lock"></i></span>
                                            <input type="password" class="form-control" name="new_password" required id="newPassword">
                                        </div>
                                        <div class="password-strength mt-2">
                                            <div class="progress" style="height: 5px;">
                                                <div class="progress-bar" role="progressbar" style="width: 0%"></div>
                                            </div>
                                            <small class="text-muted password-strength-text">Password strength: <span>Weak</span></small>
                                        </div>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label class="form-label">Confirm New Password</label>
                                        <div class="input-group">
                                            <span class="input-group-text"><i class="fas fa-lock"></i></span>
                                            <input type="password" class="form-control" name="confirm_password" required>
                                        </div>
                                    </div>
                                    
                                    <div class="text-end">
                                        <button type="submit" class="btn btn-primary px-4">
                                            <span class="submit-text">Update Password</span>
                                            <span class="spinner-border spinner-border-sm d-none" role="status"></span>
                                        </button>
                                    </div>
                                </form>
                            </div>
                            
                            <div class="border-top pt-4">
                                <h5 class="fw-bold mb-3"><i class="fas fa-shield-alt me-2"></i>Two-Factor Authentication</h5>
                                <div class="d-flex justify-content-between align-items-center p-3 bg-light rounded">
                                    <div>
                                        <h6 class="mb-1">SMS Authentication</h6>
                                        <p class="text-muted small mb-0">Add an extra layer of security to your account</p>
                                    </div>
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" id="twoFactorSms" <?= ($userData['two_factor_sms'] ?? false) ? 'checked' : '' ?> style="width: 3em; height: 1.5em;">
                                        <label class="form-check-label" for="twoFactorSms"></label>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Activity Tab -->
                        <div class="tab-pane fade" id="activity" role="tabpanel">
                            <h5 class="fw-bold mb-3"><i class="fas fa-history me-2"></i>Recent Activity</h5>
                            <div class="list-group">
                                <?php if (!empty($userData['activity'])): ?>
                                    <?php foreach ($userData['activity'] as $index => $activity): ?>
                                        <div class="list-group-item border-0 px-0 py-3 activity-item" style="animation-delay: <?= $index * 0.1 ?>s">
                                            <div class="d-flex align-items-center">
                                                <div class="flex-shrink-0 bg-light rounded-circle p-2 me-3">
                                                    <i class="fas <?= $activity['icon'] ?> text-primary"></i>
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
                <form id="avatarForm" enctype="multipart/form-data">
                    <div class="mb-3">
                        <label for="avatarUpload" class="form-label">Choose an image</label>
                        <input class="form-control" type="file" id="avatarUpload" accept="image/*">
                    </div>
                    <div class="text-center mb-3">
                        <img id="avatarPreview" src="<?= htmlspecialchars($userData['avatar'] ?? 'assets/images/default-avatar.png') ?>" 
                             class="rounded-circle shadow" 
                             style="width: 150px; height: 150px; object-fit: cover;">
                    </div>
                    <div class="text-end">
                        <button type="button" class="btn btn-outline-secondary me-2" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">
                            <span class="submit-text">Save Changes</span>
                            <span class="spinner-border spinner-border-sm d-none" role="status"></span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Password strength indicator
    const newPassword = document.getElementById('newPassword');
    if (newPassword) {
        newPassword.addEventListener('input', function() {
            const strength = calculatePasswordStrength(this.value);
            const progressBar = document.querySelector('.password-strength .progress-bar');
            const strengthText = document.querySelector('.password-strength-text span');
            
            progressBar.style.width = strength.percentage + '%';
            progressBar.className = 'progress-bar bg-' + strength.color;
            strengthText.textContent = strength.text;
        });
        
        function calculatePasswordStrength(password) {
            let strength = 0;
            
            // Length check
            if (password.length > 0) strength += 10;
            if (password.length >= 8) strength += 20;
            if (password.length >= 12) strength += 20;
            
            // Complexity checks
            if (/[A-Z]/.test(password)) strength += 10;
            if (/[0-9]/.test(password)) strength += 10;
            if (/[^A-Za-z0-9]/.test(password)) strength += 10;
            
            // Common pattern penalty
            if (/(.)\1{2,}/.test(password)) strength -= 10;
            if (password.toLowerCase() === 'password') strength = 0;
            
            // Determine strength level
            if (strength <= 30) {
                return { percentage: strength, color: 'danger', text: 'Weak' };
            } else if (strength <= 70) {
                return { percentage: strength, color: 'warning', text: 'Moderate' };
            } else {
                return { percentage: strength, color: 'success', text: 'Strong' };
            }
        }
    }
    
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
    
    // Form submissions with AJAX and loading indicators
    function setupFormSubmit(formId, successCallback) {
        const form = document.getElementById(formId);
        if (form) {
            form.addEventListener('submit', function(e) {
                e.preventDefault();
                
                const submitBtn = form.querySelector('button[type="submit"]');
                const submitText = submitBtn.querySelector('.submit-text');
                const spinner = submitBtn.querySelector('.spinner-border');
                
                // Show loading state
                submitText.textContent = 'Processing...';
                spinner.classList.remove('d-none');
                submitBtn.disabled = true;
                
                const formData = new FormData(this);
                
                fetch('profile.php', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.text())
                .then(html => {
                    // Show success state briefly before reload
                    submitText.textContent = 'Success!';
                    spinner.classList.remove('d-none');
                    submitBtn.classList.add('btn-success');
                    
                    setTimeout(() => {
                        if (typeof successCallback === 'function') {
                            successCallback();
                        } else {
                            window.location.reload();
                        }
                    }, 1000);
                })
                .catch(error => {
                    // Show error state
                    submitText.textContent = 'Error! Try Again';
                    spinner.classList.add('d-none');
                    submitBtn.classList.add('btn-danger');
                    
                    setTimeout(() => {
                        submitText.textContent = formId === 'personalInfoForm' ? 'Save Changes' : 'Update Password';
                        submitBtn.classList.remove('btn-danger');
                        submitBtn.disabled = false;
                    }, 2000);
                    
                    console.error('Error:', error);
                });
            });
        }
    }
    
    // Setup all forms
    setupFormSubmit('personalInfoForm');
    setupFormSubmit('passwordForm');
    setupFormSubmit('avatarForm', function() {
        // Special handling for avatar form to close modal after success
        const modal = bootstrap.Modal.getInstance(document.getElementById('avatarModal'));
        modal.hide();
        setTimeout(() => window.location.reload(), 500);
    });
    
    // Two-factor authentication toggle with animation
    const twoFactorSms = document.getElementById('twoFactorSms');
    if (twoFactorSms) {
        twoFactorSms.addEventListener('change', function() {
            const formData = new FormData();
            formData.append('toggle_two_factor', '1');
            formData.append('two_factor_enabled', this.checked ? '1' : '0');
            
            // Add loading class to switch
            this.disabled = true;
            const switchWrapper = this.closest('.form-switch');
            switchWrapper.classList.add('loading');
            
            fetch('profile.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.text())
            .then(html => {
                window.location.reload();
            })
            .catch(error => {
                this.checked = !this.checked;
                this.disabled = false;
                switchWrapper.classList.remove('loading');
                console.error('Error:', error);
            });
        });
    }
    
    // Tab change animation
    const tabLinks = document.querySelectorAll('[data-bs-toggle="tab"]');
    tabLinks.forEach(link => {
        link.addEventListener('click', function() {
            const target = document.querySelector(this.getAttribute('data-bs-target'));
            if (target) {
                target.style.opacity = 0;
                setTimeout(() => {
                    target.style.opacity = 1;
                }, 50);
            }
        });
    });
    
    // Initialize tooltips
    const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });
});
</script>

<?php include 'footer.php'; 
ob_end_flush();
?>