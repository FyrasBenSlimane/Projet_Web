<?php
/**
 * Dashboard main content - this file is included by the DashboardController
 * and will be rendered inside the main layout
 */
?>
<!-- Welcome Section -->
<section class="welcome-section">
    <h2 class="welcome-title">Welcome back, <?php echo $user['first_name']; ?>!</h2>
    <p class="welcome-subtitle">Here's what's happening with your account today.</p>
    
    <div class="d-flex flex-wrap gap-3">
        <a href="#" class="btn btn-primary">
            <?php echo $userType === 'freelancer' ? 'Find Projects' : 'Post a Project'; ?>
        </a>
        <a href="?page=profile" class="btn btn-outline-secondary">Complete Your Profile</a>
    </div>
</section>

<!-- Stats Section -->
<div class="dashboard-stats">
    <div class="stat-card">
        <div class="stat-icon blue">
            <i class="bi bi-briefcase-fill"></i>
        </div>
        <div class="stat-title">Active Projects</div>
        <div class="stat-value">12</div>
        <div class="stat-change">
            <i class="bi bi-arrow-up-short"></i> 8% from last month
        </div>
    </div>
    
    <div class="stat-card">
        <div class="stat-icon green">
            <i class="bi bi-cash-stack"></i>
        </div>
        <div class="stat-title">Total Earnings</div>
        <div class="stat-value">$2,850</div>
        <div class="stat-change">
            <i class="bi bi-arrow-up-short"></i> 12% from last month
        </div>
    </div>
    
    <div class="stat-card">
        <div class="stat-icon orange">
            <i class="bi bi-star-fill"></i>
        </div>
        <div class="stat-title">Avg. Rating</div>
        <div class="stat-value">4.8</div>
        <div class="stat-change">
            <i class="bi bi-arrow-up-short"></i> 0.2 from last month
        </div>
    </div>
    
    <div class="stat-card">
        <div class="stat-icon purple">
            <i class="bi bi-clock-fill"></i>
        </div>
        <div class="stat-title">Hours Worked</div>
        <div class="stat-value">187</div>
        <div class="stat-change negative">
            <i class="bi bi-arrow-down-short"></i> 5% from last month
        </div>
    </div>
</div>

<!-- Recent Projects Section -->
<section class="dashboard-table-section">
    <div class="dashboard-table-header">
        <h3 class="dashboard-table-title">Recent Projects</h3>
        <a href="#" class="dashboard-table-action">View All</a>
    </div>
    
    <div class="table-responsive">
        <table class="table">
            <thead>
                <tr>
                    <th>Project Name</th>
                    <th>Client</th>
                    <th>Due Date</th>
                    <th>Status</th>
                    <th>Budget</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>Website Redesign</td>
                    <td>TechCorp Inc.</td>
                    <td>May 15, 2025</td>
                    <td><span class="status-badge in-progress">In Progress</span></td>
                    <td>$1,500</td>
                </tr>
                <tr>
                    <td>Mobile App Development</td>
                    <td>StartUp Labs</td>
                    <td>June 28, 2025</td>
                    <td><span class="status-badge in-progress">In Progress</span></td>
                    <td>$3,200</td>
                </tr>
                <tr>
                    <td>Logo Design Package</td>
                    <td>Creative Agency</td>
                    <td>April 30, 2025</td>
                    <td><span class="status-badge completed">Completed</span></td>
                    <td>$800</td>
                </tr>
                <tr>
                    <td>SEO Optimization</td>
                    <td>E-commerce Shop</td>
                    <td>May 10, 2025</td>
                    <td><span class="status-badge on-hold">On Hold</span></td>
                    <td>$1,200</td>
                </tr>
                <tr>
                    <td>Content Writing</td>
                    <td>Blog Network</td>
                    <td>April 25, 2025</td>
                    <td><span class="status-badge completed">Completed</span></td>
                    <td>$500</td>
                </tr>
            </tbody>
        </table>
    </div>
</section>

<!-- Recent Messages Section -->
<section class="dashboard-table-section">
    <div class="dashboard-table-header">
        <h3 class="dashboard-table-title">Recent Messages</h3>
        <a href="#" class="dashboard-table-action">View All</a>
    </div>
    
    <div class="table-responsive">
        <table class="table">
            <thead>
                <tr>
                    <th>Sender</th>
                    <th>Subject</th>
                    <th>Message</th>
                    <th>Date</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>John Smith</td>
                    <td>Project Update</td>
                    <td>Hey, I wanted to check on the progress of...</td>
                    <td>Today</td>
                    <td><a href="#" class="btn btn-sm btn-outline-primary">Reply</a></td>
                </tr>
                <tr>
                    <td>Sarah Johnson</td>
                    <td>New Opportunity</td>
                    <td>I have a new project that might interest you...</td>
                    <td>Yesterday</td>
                    <td><a href="#" class="btn btn-sm btn-outline-primary">Reply</a></td>
                </tr>
                <tr>
                    <td>LenSi Support</td>
                    <td>Account Verification</td>
                    <td>Your account has been successfully verified...</td>
                    <td>Apr 15, 2025</td>
                    <td><a href="#" class="btn btn-sm btn-outline-primary">View</a></td>
                </tr>
            </tbody>
        </table>
    </div>
</section>