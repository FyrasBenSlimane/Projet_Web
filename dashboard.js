document.addEventListener('DOMContentLoaded', () => {
    // Check if user is logged in
    const user = JSON.parse(localStorage.getItem('currentUser'));
    if (!user) {
        // Redirect to index page with a login prompt
        window.location.href = 'index.html?login=required';
        return;
    }

    // Check if user is admin - improved detection with debugging
    const adminEmail = "support@xteam.tn";
    const isAdmin = user.email && typeof user.email === 'string' && 
                    user.email.toLowerCase().trim() === adminEmail.toLowerCase();
    
    console.log(`User email: ${user.email}`);
    console.log(`Comparing to admin email: ${adminEmail}`);
    console.log(`Is admin: ${isAdmin}`);
    
    // Set user role in data attribute for CSS targeting
    document.body.setAttribute('data-user-role', isAdmin ? 'admin' : 'user');
    
    // Explicitly update the welcome message and username elements
    const userNameElement = document.getElementById('userName');
    const userRoleElement = document.getElementById('userRole');
    const profileUserNameElement = document.getElementById('profileUserName');
    const profileUserEmailElement = document.getElementById('profileUserEmail');
    
    if (isAdmin) {
        // Force admin name update
        if (userNameElement) userNameElement.textContent = 'Administrator';
        if (userRoleElement) userRoleElement.textContent = 'System Administrator';
        
        // Update profile dropdown
        if (profileUserNameElement) profileUserNameElement.textContent = 'Administrator';
        if (profileUserEmailElement) profileUserEmailElement.textContent = adminEmail;
        
        console.log('Set admin name to: Administrator');
    } else {
        if (userNameElement) userNameElement.textContent = user.name || 'User';
        if (userRoleElement) userRoleElement.textContent = 'Marketplace User';
        
        // Update profile dropdown
        if (profileUserNameElement) profileUserNameElement.textContent = user.name || 'User';
        if (profileUserEmailElement) profileUserEmailElement.textContent = user.email || 'user@example.com';
        
        console.log(`Set user name to: ${user.name || 'User'}`);
    }
    
    // Show/hide elements based on role with smoother transition
    document.querySelectorAll('.admin-only').forEach(el => {
        el.style.display = isAdmin ? 'block' : 'none';
        el.style.opacity = isAdmin ? '1' : '0';
    });
    
    document.querySelectorAll('.user-only').forEach(el => {
        el.style.display = isAdmin ? 'none' : 'block';
        el.style.opacity = isAdmin ? '0' : '1';
    });

    // Load appropriate dashboard data
    if (isAdmin) {
        loadAdminDashboard();
        setupAdminEventListeners();
    } else {
        loadUserDashboard();
        setupUserEventListeners();
    }

    // Theme toggle
    const themeToggle = document.getElementById('themeToggle');
    themeToggle.addEventListener('click', () => {
        document.body.dataset.theme = document.body.dataset.theme === 'dark' ? 'light' : 'dark';
        themeToggle.innerHTML = document.body.dataset.theme === 'dark' ? 
            '<i class="fas fa-sun"></i>' : '<i class="fas fa-moon"></i>';
    });

    // Logout handler - both from sidebar and profile dropdown
    document.querySelectorAll('#logoutBtn, #profileLogoutBtn').forEach(btn => {
        if (btn) {
            btn.addEventListener('click', (e) => {
                e.preventDefault();
                console.log('Logout clicked');
                localStorage.removeItem('currentUser');
                window.location.href = 'index.html';
            });
        }
    });

    // Update date
    const dateToday = document.querySelector('.date-today');
    if (dateToday) {
        const options = { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' };
        dateToday.textContent = new Date().toLocaleDateString('en-US', options);
    }

    // Setup navigation
    setupNavigation();

    // Setup dropdowns and interactive elements
    setupDropdowns();
    setupSearch();
    
    // Setup draggable cards after loading the appropriate dashboard
    setupDraggableCards();
    
    // Add global style for drag state
    preventTextSelectionDuringDrag();
});

// Set up navigation between different sections
function setupNavigation() {
    const navLinks = document.querySelectorAll('.nav-link');
    
    navLinks.forEach(link => {
        link.addEventListener('click', (e) => {
            e.preventDefault();
            
            // Remove active class from all links
            navLinks.forEach(l => l.classList.remove('active'));
            
            // Add active class to clicked link
            link.classList.add('active');
            
            // Get section ID from data attribute
            const sectionId = link.getAttribute('data-section');
            
            // Hide all sections
            document.querySelectorAll('section[id^="section-"]').forEach(section => {
                section.classList.add('section-hidden');
            });
            
            // Show the selected section
            const selectedSection = document.getElementById(`section-${sectionId}`);
            if (selectedSection) {
                selectedSection.classList.remove('section-hidden');
                
                // Load section data if needed
                loadSectionData(sectionId);
            }
        });
    });
    
    // Set up tab navigation within sections
    setupTabNavigation();
}

// Set up tab navigation within sections
function setupTabNavigation() {
    const tabButtons = document.querySelectorAll('.tab-btn');
    
    tabButtons.forEach(button => {
        button.addEventListener('click', () => {
            const tabContainer = button.closest('.tab-container');
            const tabId = button.getAttribute('data-tab');
            
            // Remove active class from all buttons in this container
            tabContainer.querySelectorAll('.tab-btn').forEach(btn => {
                btn.classList.remove('active');
            });
            
            // Add active class to clicked button
            button.classList.add('active');
            
            // Hide all tab content in this container
            tabContainer.querySelectorAll('.tab-content').forEach(content => {
                content.classList.remove('active');
            });
            
            // Show the selected tab content
            const selectedTab = tabContainer.querySelector(`#${tabId}-tab`);
            if (selectedTab) {
                selectedTab.classList.add('active');
            }
        });
    });
}

// Load section specific data
function loadSectionData(sectionId) {
    console.log(`Loading data for section: ${sectionId}`);
    
    switch(sectionId) {
        case 'profiles':
            loadProfilesData();
            break;
        case 'projects':
            loadProjectsData();
            break;
        case 'payments':
            loadPaymentsData();
            break;
        case 'blog':
            loadBlogData();
            break;
        case 'support':
            loadSupportData();
            break;
        case 'jobs':
            loadJobsData();
            break;
        case 'myprofile':
            loadUserProfileData();
            break;
        // Add other sections as needed
    }
}

// Load profiles data for the Profiles Management section
function loadProfilesData() {
    const profilesTable = document.querySelector('#profilesTable tbody');
    if (!profilesTable) return;
    
    // Show loading state
    profilesTable.innerHTML = '<tr><td colspan="7" class="loading-cell">Loading profiles...</td></tr>';
    
    // Simulate API call
    setTimeout(() => {
        const sampleProfiles = [
            { id: 1, name: 'John Doe', email: 'john@example.com', type: 'Utilisateur', status: 'Actif', date: '2023-01-15' },
            { id: 2, name: 'Jane Smith', email: 'jane@example.com', type: 'Administrateur', status: 'Actif', date: '2023-02-20' },
            { id: 3, name: 'Robert Johnson', email: 'robert@example.com', type: 'Partenaire', status: 'Inactif', date: '2023-03-10' },
            { id: 4, name: 'Emily Davis', email: 'emily@example.com', type: 'Utilisateur', status: 'En attente', date: '2023-04-05' },
            { id: 5, name: 'Michael Wilson', email: 'michael@example.com', type: 'Utilisateur', status: 'Actif', date: '2023-05-12' }
        ];
        
        profilesTable.innerHTML = '';
        
        sampleProfiles.forEach(profile => {
            const row = document.createElement('tr');
            row.innerHTML = `
                <td>${profile.id}</td>
                <td>${profile.name}</td>
                <td>${profile.email}</td>
                <td>${profile.type}</td>
                <td><span class="status-badge ${profile.status.toLowerCase().replace(/\s+/g, '-')}">${profile.status}</span></td>
                <td>${profile.date}</td>
                <td>
                    <button class="action-btn" onclick="editProfile(${profile.id})"><i class="fas fa-edit"></i></button>
                    <button class="action-btn" onclick="deleteProfile(${profile.id})"><i class="fas fa-trash"></i></button>
                </td>
            `;
            profilesTable.appendChild(row);
        });
        
        // Set up add profile button
        const addProfileBtn = document.getElementById('addProfileBtn');
        if (addProfileBtn) {
            addProfileBtn.onclick = () => showProfileForm();
        }
        
        // Set up profile form modal close button
        const closeModalBtns = document.querySelectorAll('#profileFormOverlay .close-modal, #profileFormOverlay .btn-cancel');
        closeModalBtns.forEach(btn => {
            btn.onclick = () => {
                document.getElementById('profileFormOverlay').classList.remove('active');
            };
        });
        
        // Set up save profile button
        const saveProfileBtn = document.getElementById('saveProfileBtn');
        if (saveProfileBtn) {
            saveProfileBtn.onclick = () => saveProfile();
        }
        
        // Set up confirmation dialog buttons
        document.querySelectorAll('#confirmDeleteOverlay .close-modal, #confirmDeleteOverlay .btn-cancel').forEach(btn => {
            btn.onclick = () => {
                document.getElementById('confirmDeleteOverlay').classList.remove('active');
            };
        });
        
        const confirmDeleteBtn = document.getElementById('confirmDeleteBtn');
        if (confirmDeleteBtn) {
            confirmDeleteBtn.onclick = () => confirmDeleteProfile();
        }
    }, 1000);
}

// Show profile form for adding or editing
function showProfileForm(profileId = null) {
    const formTitle = document.getElementById('profileFormTitle');
    const profileForm = document.getElementById('profileForm');
    const overlay = document.getElementById('profileFormOverlay');
    
    if (profileId) {
        formTitle.textContent = 'Modifier un Profil';
        // Simulate fetching profile data
        // In a real app, you would make an API call here
        const profileData = {
            id: profileId,
            name: 'John Doe',
            email: 'john@example.com',
            type: 'user',
            status: 'active'
        };
        
        document.getElementById('profileId').value = profileData.id;
        document.getElementById('profileName').value = profileData.name;
        document.getElementById('profileEmail').value = profileData.email;
        document.getElementById('profileType').value = profileData.type;
        document.getElementById('profileStatus').value = profileData.status;
        document.getElementById('profilePassword').value = '';
    } else {
        formTitle.textContent = 'Ajouter un Profil';
        profileForm.reset();
        document.getElementById('profileId').value = '';
    }
    
    overlay.classList.add('active');
}

// Save profile (add or update)
function saveProfile() {
    const profileId = document.getElementById('profileId').value;
    const profileName = document.getElementById('profileName').value;
    const profileEmail = document.getElementById('profileEmail').value;
    const profileType = document.getElementById('profileType').value;
    const profileStatus = document.getElementById('profileStatus').value;
    const profilePassword = document.getElementById('profilePassword').value;
    
    // Validate form
    if (!profileName || !profileEmail) {
        showToast('Veuillez remplir tous les champs obligatoires.');
        return;
    }
    
    // In a real app, you would make an API call to save the profile
    console.log('Saving profile:', {
        id: profileId || 'new',
        name: profileName,
        email: profileEmail,
        type: profileType,
        status: profileStatus,
        password: profilePassword ? '********' : '(unchanged)'
    });
    
    // Close the form and reload the data
    document.getElementById('profileFormOverlay').classList.remove('active');
    
    // Show success message
    showToast(profileId ? 'Profil mis à jour avec succès.' : 'Profil ajouté avec succès.');
    
    // Reload profiles data
    loadProfilesData();
}

// Show delete confirmation dialog
function deleteProfile(profileId) {
    document.getElementById('confirmDeleteBtn').dataset.profileId = profileId;
    document.getElementById('confirmDeleteOverlay').classList.add('active');
}

// Confirm profile deletion
function confirmDeleteProfile() {
    const profileId = document.getElementById('confirmDeleteBtn').dataset.profileId;
    
    // In a real app, you would make an API call to delete the profile
    console.log('Deleting profile:', profileId);
    
    // Close the confirmation dialog
    document.getElementById('confirmDeleteOverlay').classList.remove('active');
    
    // Show success message
    showToast('Profil supprimé avec succès.');
    
    // Reload profiles data
    loadProfilesData();
}

// Load projects data for the Projects Management section
function loadProjectsData() {
    const projectsContainer = document.getElementById('projectsContainer');
    if (!projectsContainer) return;
    
    // Show loading state
    projectsContainer.innerHTML = '<div class="loading-container">Chargement des projets...</div>';
    
    // Simulate API call
    setTimeout(() => {
        const sampleProjects = [
            {
                id: 1,
                name: 'Refonte du site web',
                description: 'Modernisation complète du site web corporate avec intégration de nouvelles fonctionnalités.',
                type: 'development',
                status: 'active',
                startDate: '2023-03-15',
                endDate: '2023-06-30',
                team: ['John D.', 'Emily R.', 'Michael T.']
            },
            {
                id: 2,
                name: 'Application mobile iOS',
                description: 'Développement d\'une application iOS pour le service client avec notifications push.',
                type: 'development',
                status: 'planned',
                startDate: '2023-07-10',
                endDate: '2023-09-30',
                team: ['Sarah L.', 'David K.']
            },
            {
                id: 3,
                name: 'Campagne marketing Q3',
                description: 'Stratégie et exécution de la campagne marketing pour le 3ème trimestre 2023.',
                type: 'marketing',
                status: 'completed',
                startDate: '2023-01-05',
                endDate: '2023-03-31',
                team: ['Robert J.', 'Anna M.', 'Thomas P.']
            },
            {
                id: 4,
                name: 'Redesign UX/UI',
                description: 'Refonte de l\'expérience utilisateur et de l\'interface pour l\'application principale.',
                type: 'design',
                status: 'active',
                startDate: '2023-02-20',
                endDate: '2023-05-15',
                team: ['Jennifer S.', 'Paul R.']
            }
        ];
        
        projectsContainer.innerHTML = '';
        
        sampleProjects.forEach(project => {
            const projectCard = document.createElement('div');
            projectCard.className = 'project-card card-item';
            
            // Get status class for CSS styling
            const statusClass = project.status === 'active' ? 'in-progress' : 
                               (project.status === 'completed' ? 'completed' : 'planned');
            
            // Get status label in French
            const statusLabel = project.status === 'active' ? 'En cours' : 
                              (project.status === 'completed' ? 'Terminé' : 'Planifié');
            
            // Get type label in French
            const typeLabel = project.type === 'development' ? 'Développement' : 
                            (project.type === 'design' ? 'Design' : 'Marketing');
            
            projectCard.innerHTML = `
                <div class="project-status">
                    <span class="status-badge ${statusClass}">${statusLabel}</span>
                </div>
                <div class="project-image">
                    <i class="${project.type === 'development' ? 'fas fa-code' : 
                              (project.type === 'design' ? 'fas fa-paint-brush' : 'fas fa-chart-line')}"></i>
                </div>
                <div class="project-info">
                    <h3 class="project-title">${project.name}</h3>
                    <p class="project-description">${project.description}</p>
                </div>
                <div class="project-meta">
                    <span>${typeLabel}</span>
                    <span>${formatDate(project.startDate)} - ${formatDate(project.endDate)}</span>
                </div>
                <div class="project-team">
                    ${project.team.map((member, index) => 
                        `<div class="team-member" title="${member}" style="z-index: ${10-index}">
                            ${member.split(' ')[0][0]}${member.split(' ')[1]?.[0] || ''}
                        </div>`
                    ).join('')}
                </div>
                <div class="project-actions">
                    <button class="action-btn" onclick="editProject(${project.id})"><i class="fas fa-edit"></i></button>
                    <button class="action-btn" onclick="deleteProject(${project.id})"><i class="fas fa-trash"></i></button>
                    <button class="action-btn" onclick="viewProjectDetails(${project.id})"><i class="fas fa-eye"></i></button>
                </div>
            `;
            
            projectsContainer.appendChild(projectCard);
        });
        
        // Set up add project button
        const addProjectBtn = document.getElementById('addProjectBtn');
        if (addProjectBtn) {
            addProjectBtn.onclick = () => showProjectForm();
        }
        
        // Set up project form modal close button
        const closeModalBtns = document.querySelectorAll('#projectFormOverlay .close-modal, #projectFormOverlay .btn-cancel');
        closeModalBtns.forEach(btn => {
            btn.onclick = () => {
                document.getElementById('projectFormOverlay').classList.remove('active');
            };
        });
        
        // Set up save project button
        const saveProjectBtn = document.getElementById('saveProjectBtn');
        if (saveProjectBtn) {
            saveProjectBtn.onclick = () => saveProject();
        }
    }, 1000);
}

// Format date helper function
function formatDate(dateString) {
    const options = { day: '2-digit', month: '2-digit', year: 'numeric' };
    return new Date(dateString).toLocaleDateString('fr-FR', options);
}

// Show project form for adding or editing
function showProjectForm(projectId = null) {
    const formTitle = document.getElementById('projectFormTitle');
    const projectForm = document.getElementById('projectForm');
    const overlay = document.getElementById('projectFormOverlay');
    
    if (projectId) {
        formTitle.textContent = 'Modifier un Projet';
        // Simulate fetching project data
        // In a real app, you would make an API call here
        const projectData = {
            id: projectId,
            name: 'Refonte du site web',
            description: 'Modernisation complète du site web corporate avec intégration de nouvelles fonctionnalités.',
            type: 'development',
            status: 'active',
            startDate: '2023-03-15',
            endDate: '2023-06-30',
            team: ['1', '3', '5'] // Assume these are user IDs
        };
        
        document.getElementById('projectId').value = projectData.id;
        document.getElementById('projectName').value = projectData.name;
        document.getElementById('projectDescription').value = projectData.description;
        document.getElementById('projectType').value = projectData.type;
        document.getElementById('projectStatus').value = projectData.status;
        document.getElementById('projectStartDate').value = projectData.startDate;
        document.getElementById('projectEndDate').value = projectData.endDate;
        
        // Set selected team members (would be populated from API in a real app)
        const teamSelect = document.getElementById('projectTeam');
        if (teamSelect && projectData.team) {
            for (let i = 0; i < teamSelect.options.length; i++) {
                teamSelect.options[i].selected = projectData.team.includes(teamSelect.options[i].value);
            }
        }
    } else {
        formTitle.textContent = 'Créer un Projet';
        projectForm.reset();
        document.getElementById('projectId').value = '';
    }
    
    overlay.classList.add('active');
}

// Save project (add or update)
function saveProject() {
    const projectId = document.getElementById('projectId').value;
    const projectName = document.getElementById('projectName').value;
    const projectDescription = document.getElementById('projectDescription').value;
    const projectType = document.getElementById('projectType').value;
    const projectStatus = document.getElementById('projectStatus').value;
    const projectStartDate = document.getElementById('projectStartDate').value;
    const projectEndDate = document.getElementById('projectEndDate').value;
    
    // Get selected team members
    const teamSelect = document.getElementById('projectTeam');
    const selectedTeam = [];
    if (teamSelect) {
        for (let i = 0; i < teamSelect.options.length; i++) {
            if (teamSelect.options[i].selected) {
                selectedTeam.push(teamSelect.options[i].value);
            }
        }
    }
    
    // Validate form
    if (!projectName || !projectDescription || !projectStartDate) {
        showToast('Veuillez remplir tous les champs obligatoires.');
        return;
    }
    
    // In a real app, you would make an API call to save the project
    console.log('Saving project:', {
        id: projectId || 'new',
        name: projectName,
        description: projectDescription,
        type: projectType,
        status: projectStatus,
        startDate: projectStartDate,
        endDate: projectEndDate,
        team: selectedTeam
    });
    
    // Close the form and reload the data
    document.getElementById('projectFormOverlay').classList.remove('active');
    
    // Show success message
    showToast(projectId ? 'Projet mis à jour avec succès.' : 'Projet créé avec succès.');
    
    // Reload projects data
    loadProjectsData();
}

// Edit a project
function editProject(projectId) {
    showProjectForm(projectId);
}

// Delete a project
function deleteProject(projectId) {
    // In a real app, you would show a confirmation dialog
    if (confirm('Êtes-vous sûr de vouloir supprimer ce projet?')) {
        // Then make an API call to delete the project
        console.log('Deleting project:', projectId);
        
        // Show success message
        showToast('Projet supprimé avec succès.');
        
        // Reload projects data
        loadProjectsData();
    }
}

// View project details
function viewProjectDetails(projectId) {
    console.log('Viewing project details:', projectId);
    // In a real app, you would show a detailed view of the project
    showToast('Affichage des détails du projet...');
}

// Load payments data
function loadPaymentsData() {
    // Update statistics
    document.getElementById('totalRevenueStat').textContent = '156,789 €';
    document.getElementById('monthlyTransactionsStat').textContent = '487';
    document.getElementById('pendingBalanceStat').textContent = '12,450 €';
    document.getElementById('unpaidInvoicesStat').textContent = '24';
    
    // Load transactions data
    loadTransactionsData();
    
    // Set up tab buttons
    document.querySelectorAll('#section-payments .tab-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            const tabId = this.getAttribute('data-tab');
            
            if (tabId === 'transactions') {
                loadTransactionsData();
            } else if (tabId === 'invoices') {
                loadInvoicesData();
            } else if (tabId === 'payment-methods') {
                loadPaymentMethodsData();
            }
        });
    });
}

// Load transactions data
function loadTransactionsData() {
    const transactionsTable = document.querySelector('#transactionsTable tbody');
    if (!transactionsTable) return;
    
    // Show loading state
    transactionsTable.innerHTML = '<tr><td colspan="8" class="loading-cell">Chargement des transactions...</td></tr>';
    
    // Simulate API call
    setTimeout(() => {
        const sampleTransactions = [
            { id: 'TX-001', date: '2023-05-15', client: 'Martin Dubois', amount: '1,250.00 €', type: 'income', status: 'completed', method: 'Carte bancaire' },
            { id: 'TX-002', date: '2023-05-12', client: 'Sophie Leroux', amount: '750.00 €', type: 'income', status: 'pending', method: 'Virement bancaire' },
            { id: 'TX-003', date: '2023-05-10', client: 'Julien Petit', amount: '350.00 €', type: 'income', status: 'completed', method: 'PayPal' },
            { id: 'TX-004', date: '2023-05-05', client: 'Fournisseur A', amount: '-2,500.00 €', type: 'expense', status: 'completed', method: 'Virement bancaire' },
            { id: 'TX-005', date: '2023-05-03', client: 'Marie Laurent', amount: '950.00 €', type: 'income', status: 'completed', method: 'Carte bancaire' },
            { id: 'TX-006', date: '2023-04-29', client: 'Thomas Moreau', amount: '180.00 €', type: 'refund', status: 'completed', method: 'Carte bancaire' }
        ];
        
        transactionsTable.innerHTML = '';
        
        sampleTransactions.forEach(transaction => {
            const row = document.createElement('tr');
            
            // Determine status and type classes for styling
            const statusClass = transaction.status === 'completed' ? 'completed' : 
                              (transaction.status === 'pending' ? 'pending' : 'in-progress');
            
            const typeClass = transaction.type === 'income' ? 'active' :
                            (transaction.type === 'expense' ? 'inactive' : 'pending');
            
            // Translate status and type to French
            const statusLabel = transaction.status === 'completed' ? 'Complété' :
                              (transaction.status === 'pending' ? 'En attente' : 'En cours');
            
            const typeLabel = transaction.type === 'income' ? 'Revenu' :
                            (transaction.type === 'expense' ? 'Dépense' : 'Remboursement');
            
            row.innerHTML = `
                <td>${transaction.id}</td>
                <td>${formatDate(transaction.date)}</td>
                <td>${transaction.client}</td>
                <td class="${transaction.type === 'expense' ? 'text-danger' : 'text-success'}">${transaction.amount}</td>
                <td><span class="status-badge ${typeClass}">${typeLabel}</span></td>
                <td><span class="status-badge ${statusClass}">${statusLabel}</span></td>
                <td>${transaction.method}</td>
                <td>
                    <button class="action-btn" onclick="viewTransaction('${transaction.id}')"><i class="fas fa-eye"></i></button>
                    <button class="action-btn" onclick="exportTransactionPdf('${transaction.id}')"><i class="fas fa-file-pdf"></i></button>
                </td>
            `;
            
            transactionsTable.appendChild(row);
        });
        
        // Set up export button
        const exportBtn = document.getElementById('exportTransactionsBtn');
        if (exportBtn) {
            exportBtn.onclick = () => exportTransactions();
        }
    }, 1000);
}

// View transaction details
function viewTransaction(id) {
    console.log('Viewing transaction:', id);
    // In a real app, you would show a detailed view of the transaction
    showToast('Affichage des détails de la transaction...');
}

// Export transaction as PDF
function exportTransactionPdf(id) {
    console.log('Exporting transaction as PDF:', id);
    // In a real app, you would generate and download a PDF
    showToast('Export de la transaction au format PDF...');
}

// Export all transactions
function exportTransactions() {
    console.log('Exporting all transactions');
    // In a real app, you would generate and download a file
    showToast('Export de toutes les transactions...');
}

// Load blog data
function loadBlogData() {
    // Default to the posts tab
    loadPostsData();
    
    // Set up tab buttons
    document.querySelectorAll('#section-blog .tab-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            const tabId = this.getAttribute('data-tab');
            
            if (tabId === 'posts') {
                loadPostsData();
            } else if (tabId === 'categories') {
                loadCategoriesData();
            } else if (tabId === 'resources') {
                loadResourcesData();
            }
        });
    });
}

// Load posts data
function loadPostsData() {
    const postsTable = document.querySelector('#postsTable tbody');
    if (!postsTable) return;
    
    // Show loading state
    postsTable.innerHTML = '<tr><td colspan="7" class="loading-cell">Chargement des articles...</td></tr>';
    
    // Simulate API call
    setTimeout(() => {
        const samplePosts = [
            { id: 1, title: 'Comment optimiser votre site web pour le SEO', category: 'Stratégie digitale', author: 'Sophie Martin', date: '2023-05-10', status: 'published', views: 1250 },
            { id: 2, title: 'Les tendances UX/UI pour 2023', category: 'Design', author: 'Thomas Dubois', date: '2023-04-28', status: 'published', views: 860 },
            { id: 3, title: 'Guide complet du marketing automation', category: 'Marketing', author: 'Julie Leroy', date: '2023-05-15', status: 'draft', views: 0 },
            { id: 4, title: 'Comment protéger vos données en ligne', category: 'Sécurité', author: 'Marc Petit', date: '2023-06-02', status: 'scheduled', views: 0 },
            { id: 5, title: 'Les meilleures pratiques pour le développement mobile', category: 'Développement', author: 'Sophie Martin', date: '2023-04-15', status: 'published', views: 752 }
        ];
        
        postsTable.innerHTML = '';
        
        samplePosts.forEach(post => {
            const row = document.createElement('tr');
            
            // Determine status class for styling
            const statusClass = post.status === 'published' ? 'active' :
                             (post.status === 'draft' ? 'inactive' : 'pending');
            
            // Translate status to French
            const statusLabel = post.status === 'published' ? 'Publié' :
                             (post.status === 'draft' ? 'Brouillon' : 'Programmé');
            
            row.innerHTML = `
                <td>${post.title}</td>
                <td>${post.category}</td>
                <td>${post.author}</td>
                <td>${formatDate(post.date)}</td>
                <td><span class="status-badge ${statusClass}">${statusLabel}</span></td>
                <td>${post.views}</td>
                <td>
                    <button class="action-btn" onclick="editPost(${post.id})"><i class="fas fa-edit"></i></button>
                    <button class="action-btn" onclick="deletePost(${post.id})"><i class="fas fa-trash"></i></button>
                    <button class="action-btn" onclick="viewPost(${post.id})"><i class="fas fa-eye"></i></button>
                </td>
            `;
            
            postsTable.appendChild(row);
        });
        
        // Set up add post button
        const addPostBtn = document.getElementById('addPostBtn');
        if (addPostBtn) {
            addPostBtn.onclick = () => showPostForm();
        }
        
        // Populate category filter (in a real app, you would get this from API)
        const categoryFilter = document.getElementById('postCategoryFilter');
        if (categoryFilter) {
            const categories = ['Stratégie digitale', 'Design', 'Marketing', 'Sécurité', 'Développement'];
            categoryFilter.innerHTML = '<option value="all">Toutes les catégories</option>';
            
            categories.forEach(category => {
                const option = document.createElement('option');
                option.value = category.toLowerCase();
                option.textContent = category;
                categoryFilter.appendChild(option);
            });
        }
    }, 1000);
}

// Show post form for adding or editing
function showPostForm(postId = null) {
    // In a real app, you would show a form to add or edit a post
    console.log('Showing post form for post ID:', postId || 'new');
    showToast(postId ? 'Modification de l\'article...' : 'Création d\'un nouvel article...');
}

// Edit a post
function editPost(postId) {
    showPostForm(postId);
}

// Delete a post
function deletePost(postId) {
    // In a real app, you would show a confirmation dialog
    if (confirm('Êtes-vous sûr de vouloir supprimer cet article?')) {
        // Then make an API call to delete the post
        console.log('Deleting post:', postId);
        
        // Show success message
        showToast('Article supprimé avec succès.');
        
        // Reload posts data
        loadPostsData();
    }
}

// View a post
function viewPost(postId) {
    console.log('Viewing post:', postId);
    // In a real app, you would open the post in a new tab or show a detailed view
    showToast('Affichage de l\'article...');
}

// Load support data
function loadSupportData() {
    // Update statistics
    document.getElementById('openTicketsStat').textContent = '32';
    document.getElementById('responseTimeStat').textContent = '4.5h';
    document.getElementById('resolvedTicketsStat').textContent = '187';
    document.getElementById('satisfactionRateStat').textContent = '94%';
    
    // Default to the tickets tab
    loadTicketsData();
    
    // Set up tab buttons
    document.querySelectorAll('#section-support .tab-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            const tabId = this.getAttribute('data-tab');
            
            if (tabId === 'tickets') {
                loadTicketsData();
            } else if (tabId === 'faq') {
                loadFaqData();
            } else if (tabId === 'knowledge-base') {
                loadKnowledgeBaseData();
            }
        });
    });
}

// Load tickets data
function loadTicketsData() {
    const ticketsTable = document.querySelector('#ticketsTable tbody');
    if (!ticketsTable) return;
    
    // Show loading state
    ticketsTable.innerHTML = '<tr><td colspan="8" class="loading-cell">Chargement des tickets...</td></tr>';
    
    // Simulate API call
    setTimeout(() => {
        const sampleTickets = [
            { id: 'TCK-1001', subject: 'Problème de connexion au compte', user: 'Jean Dupont', date: '2023-05-18', lastResponse: '2023-05-18', priority: 'high', status: 'open' },
            { id: 'TCK-1002', subject: 'Question sur la facturation', user: 'Marie Lambert', date: '2023-05-17', lastResponse: '2023-05-17', priority: 'medium', status: 'in-progress' },
            { id: 'TCK-1003', subject: 'Demande de fonctionnalité', user: 'Paul Bernard', date: '2023-05-15', lastResponse: '2023-05-16', priority: 'low', status: 'open' },
            { id: 'TCK-1004', subject: 'Bug dans l\'interface utilisateur', user: 'Sophie Martin', date: '2023-05-12', lastResponse: '2023-05-14', priority: 'high', status: 'in-progress' },
            { id: 'TCK-1005', subject: 'Problème de paiement', user: 'Robert Petit', date: '2023-05-10', lastResponse: '2023-05-15', priority: 'urgent', status: 'open' },
            { id: 'TCK-1006', subject: 'Question sur l\'utilisation', user: 'Emilie Renaud', date: '2023-05-08', lastResponse: '2023-05-10', priority: 'medium', status: 'resolved' },
            { id: 'TCK-1007', subject: 'Suggestion d\'amélioration', user: 'Thomas Dubois', date: '2023-05-05', lastResponse: '2023-05-06', priority: 'low', status: 'closed' }
        ];
        
        ticketsTable.innerHTML = '';
        
        sampleTickets.forEach(ticket => {
            const row = document.createElement('tr');
            
            // Determine status and priority classes for styling
            const statusClass = ticket.status === 'open' ? 'open' :
                             (ticket.status === 'in-progress' ? 'in-progress' :
                              (ticket.status === 'resolved' ? 'completed' : 'inactive'));
            
            const priorityClass = ticket.priority === 'urgent' ? 'urgent' :
                               (ticket.priority === 'high' ? 'open' :
                                (ticket.priority === 'medium' ? 'pending' : 'inactive'));
            
            // Translate status and priority to French
            const statusLabel = ticket.status === 'open' ? 'Ouvert' :
                             (ticket.status === 'in-progress' ? 'En cours' :
                              (ticket.status === 'resolved' ? 'Résolu' : 'Fermé'));
            
            const priorityLabel = ticket.priority === 'urgent' ? 'Urgente' :
                               (ticket.priority === 'high' ? 'Haute' :
                                (ticket.priority === 'medium' ? 'Moyenne' : 'Basse'));
            
            row.innerHTML = `
                <td>${ticket.id}</td>
                <td>${ticket.subject}</td>
                <td>${ticket.user}</td>
                <td>${formatDate(ticket.date)}</td>
                <td>${formatDate(ticket.lastResponse)}</td>
                <td><span class="status-badge ${priorityClass}">${priorityLabel}</span></td>
                <td><span class="status-badge ${statusClass}">${statusLabel}</span></td>
                <td>
                    <button class="action-btn" onclick="viewTicket('${ticket.id}')"><i class="fas fa-eye"></i></button>
                    <button class="action-btn" onclick="respondToTicket('${ticket.id}')"><i class="fas fa-reply"></i></button>
                    <button class="action-btn" onclick="changeTicketStatus('${ticket.id}')"><i class="fas fa-exchange-alt"></i></button>
                </td>
            `;
            
            ticketsTable.appendChild(row);
        });
    }, 1000);
}

// View ticket details
function viewTicket(id) {
    console.log('Viewing ticket:', id);
    // In a real app, you would show a detailed view of the ticket
    showToast('Affichage des détails du ticket...');
}

// Respond to a ticket
function respondToTicket(id) {
    console.log('Responding to ticket:', id);
    // In a real app, you would show a form to respond to the ticket
    showToast('Réponse au ticket...');
}

// Change ticket status
function changeTicketStatus(id) {
    console.log('Changing status for ticket:', id);
    // In a real app, you would show a dropdown or modal to change the status
    showToast('Modification du statut du ticket...');
}

// Load jobs data
function loadJobsData() {
    // Default to the job offers tab
    loadJobOffersData();
    
    // Set up tab buttons
    document.querySelectorAll('#section-jobs .tab-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            const tabId = this.getAttribute('data-tab');
            
            if (tabId === 'job-offers') {
                loadJobOffersData();
            } else if (tabId === 'applications') {
                loadApplicationsData();
            } else if (tabId === 'reports') {
                loadJobReportsData();
            }
        });
    });
}

// Load job offers data
function loadJobOffersData() {
    const jobOffersContainer = document.getElementById('jobOffersContainer');
    if (!jobOffersContainer) return;
    
    // Show loading state
    jobOffersContainer.innerHTML = '<div class="loading-container">Chargement des offres d\'emploi...</div>';
    
    // Simulate API call
    setTimeout(() => {
        const sampleJobs = [
            {
                id: 1,
                title: 'Développeur Full Stack',
                department: 'development',
                location: 'Paris, France',
                type: 'CDI',
                salary: '45K-55K €',
                experience: '3-5 ans',
                description: 'Nous recherchons un développeur Full Stack expérimenté pour rejoindre notre équipe technique...',
                status: 'active',
                postedDate: '2023-05-01',
                applications: 24
            },
            {
                id: 2,
                title: 'UI/UX Designer',
                department: 'design',
                location: 'Lyon, France',
                type: 'CDI',
                salary: '38K-48K €',
                experience: '2-4 ans',
                description: 'Rejoignez notre équipe de design pour créer des expériences utilisateur exceptionnelles...',
                status: 'active',
                postedDate: '2023-05-05',
                applications: 18
            },
            {
                id: 3,
                title: 'Chef de Projet Digital',
                department: 'marketing',
                location: 'Paris, France',
                type: 'CDI',
                salary: '50K-60K €',
                experience: '5+ ans',
                description: 'Nous cherchons un chef de projet expérimenté pour piloter nos initiatives digitales...',
                status: 'active',
                postedDate: '2023-05-10',
                applications: 15
            },
            {
                id: 4,
                title: 'Commercial B2B',
                department: 'sales',
                location: 'Marseille, France',
                type: 'CDI',
                salary: '35K-45K € + commissions',
                experience: '2+ ans',
                description: 'Rejoignez notre équipe commerciale en pleine croissance...',
                status: 'active',
                postedDate: '2023-05-12',
                applications: 9
            },
            {
                id: 5,
                title: 'Développeur Back-End',
                department: 'development',
                location: 'Remote',
                type: 'Freelance',
                salary: 'Selon profil',
                experience: '3+ ans',
                description: 'Nous recherchons un développeur Back-End pour un projet de 6 mois...',
                status: 'active',
                postedDate: '2023-05-15',
                applications: 12
            }
        ];
        
        jobOffersContainer.innerHTML = '';
        
        sampleJobs.forEach(job => {
            const jobCard = document.createElement('div');
            jobCard.className = 'job-card card-item';
            
            // Get department label in French
            const departmentLabel = job.department === 'development' ? 'Développement' :
                                 (job.department === 'design' ? 'Design' :
                                  (job.department === 'marketing' ? 'Marketing' :
                                   (job.department === 'sales' ? 'Ventes' : 'Support')));
            
            // Calculate days ago
            const postedDate = new Date(job.postedDate);
            const today = new Date();
            const diffTime = Math.abs(today - postedDate);
            const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24));
            
            jobCard.innerHTML = `
                <div class="job-department">
                    ${departmentLabel}
                </div>
                <div class="job-info">
                    <h3 class="job-title">${job.title}</h3>
                    <div class="job-location">
                        <i class="fas fa-map-marker-alt"></i> ${job.location}
                    </div>
                    <div class="job-details">
                        <span class="job-detail"><i class="fas fa-briefcase"></i> ${job.type}</span>
                        <span class="job-detail"><i class="fas fa-euro-sign"></i> ${job.salary}</span>
                        <span class="job-detail"><i class="fas fa-user-clock"></i> ${job.experience}</span>
                    </div>
                    <p class="job-description">${job.description}</p>
                </div>
                <div class="job-footer">
                    <span class="job-meta">
                        <i class="fas fa-users"></i> ${job.applications} candidatures
                    </span>
                    <span class="job-meta">
                        <i class="fas fa-clock"></i> Il y a ${diffDays} jour${diffDays > 1 ? 's' : ''}
                    </span>
                </div>
                <div class="job-actions">
                    <button class="action-btn primary" onclick="editJob(${job.id})"><i class="fas fa-edit"></i> Modifier</button>
                    <button class="action-btn" onclick="viewApplications(${job.id})"><i class="fas fa-users"></i> Candidatures</button>
                    <button class="action-btn" onclick="deleteJob(${job.id})"><i class="fas fa-trash"></i></button>
                </div>
            `;
            
            jobOffersContainer.appendChild(jobCard);
        });
        
        // Set up add job button
        const addJobBtn = document.getElementById('addJobBtn');
        if (addJobBtn) {
            addJobBtn.onclick = () => showJobForm();
        }
    }, 1000);
}

// Show job form for adding or editing
function showJobForm(jobId = null) {
    // In a real app, you would show a form to add or edit a job
    console.log('Showing job form for job ID:', jobId || 'new');
    showToast(jobId ? 'Modification de l\'offre d\'emploi...' : 'Création d\'une nouvelle offre d\'emploi...');
}

// Edit a job
function editJob(jobId) {
    showJobForm(jobId);
}

// Delete a job
function deleteJob(jobId) {
    // In a real app, you would show a confirmation dialog
    if (confirm('Êtes-vous sûr de vouloir supprimer cette offre d\'emploi?')) {
        // Then make an API call to delete the job
        console.log('Deleting job:', jobId);
        
        // Show success message
        showToast('Offre d\'emploi supprimée avec succès.');
        
        // Reload job offers data
        loadJobOffersData();
    }
}

// View applications for a job
function viewApplications(jobId) {
    console.log('Viewing applications for job:', jobId);
    
    // Switch to the applications tab
    document.querySelector('#section-jobs .tab-btn[data-tab="applications"]').click();
    
    // In a real app, you would filter the applications by job ID
    showToast('Affichage des candidatures pour l\'offre d\'emploi...');
}

// Setup dropdown functionality (notifications and profile)
function setupDropdowns() {
    const notificationBell = document.querySelector('.notification-bell');
    const userProfile = document.querySelector('.user-profile');
    
    if (notificationBell) {
        notificationBell.addEventListener('click', (e) => {
            e.stopPropagation();
            notificationBell.classList.toggle('active');
            
            if (userProfile) userProfile.classList.remove('active');
            
            // Load notifications when opened
            if (notificationBell.classList.contains('active')) {
                loadNotifications();
            }
        });
    }
    
    if (userProfile) {
        userProfile.addEventListener('click', (e) => {
            e.stopPropagation();
            userProfile.classList.toggle('active');
            
            if (notificationBell) notificationBell.classList.remove('active');
        });
    }
    
    // Close dropdowns when clicking elsewhere
    document.addEventListener('click', () => {
        if (notificationBell) notificationBell.classList.remove('active');
        if (userProfile) userProfile.classList.remove('active');
    });
    
    // Mark all notifications as read
    const markAllReadBtn = document.querySelector('.mark-all-read');
    if (markAllReadBtn) {
        markAllReadBtn.addEventListener('click', () => {
            const notificationItems = document.querySelectorAll('.notification-item');
            notificationItems.forEach(item => {
                item.classList.remove('unread');
            });
            
            const badge = document.querySelector('.notification-badge');
            if (badge) badge.textContent = '0';
        });
    }
}

// Setup search functionality
function setupSearch() {
    const searchInput = document.querySelector('.search-bar input');
    const searchResults = document.querySelector('.search-results-container');
    const searchCategories = document.querySelectorAll('.search-categories button');
    
    if (!searchInput || !searchResults) return;
    
    searchInput.addEventListener('input', (e) => {
        const searchTerm = e.target.value;
        if (searchTerm.length > 1) {
            performSearch(searchTerm);
        } else {
            searchResults.innerHTML = '<div class="no-results">Type at least 2 characters to search</div>';
        }
    });
    
    if (searchCategories) {
        searchCategories.forEach(button => {
            button.addEventListener('click', () => {
                searchCategories.forEach(btn => btn.classList.remove('active'));
                button.classList.add('active');
                
                if (searchInput.value.length > 1) {
                    performSearch(searchInput.value, button.textContent.toLowerCase());
                }
            });
        });
    }
}

// New function for admin-specific event listeners
function setupAdminEventListeners() {
    // Add user button
    const addUserBtn = document.querySelector('.btn-add');
    if (addUserBtn) {
        addUserBtn.addEventListener('click', () => {
            console.log('Add user clicked - would show modal in production');
            // Here you would open a modal to add a new user
        });
    }

    // Action buttons in user management table
    document.querySelectorAll('.action-btn').forEach(btn => {
        btn.addEventListener('click', (e) => {
            const action = e.currentTarget.querySelector('i').classList.contains('fa-edit') ? 'edit' : 'delete';
            const row = e.currentTarget.closest('tr');
            const userId = row.querySelector('td:first-child').textContent;
            
            if (action === 'edit') {
                console.log(`Edit user ${userId}`);
                // Open edit user modal
            } else {
                console.log(`Delete user ${userId}`);
                // Show delete confirmation
            }
        });
    });
    
    // Setup profile page navigation
    setupProfileTabs();
}

// Setup profile page tabs
function setupProfileTabs() {
    const profileTabs = document.querySelectorAll('.profile-nav a');
    
    profileTabs.forEach(tab => {
        tab.addEventListener('click', (e) => {
            e.preventDefault();
            
            // Remove active class from all tabs
            profileTabs.forEach(t => t.classList.remove('active'));
            
            // Add active class to clicked tab
            tab.classList.add('active');
            
            // Hide all tab content
            document.querySelectorAll('.profile-tab').forEach(content => {
                content.classList.remove('active');
            });
            
            // Show the selected tab content
            const tabId = tab.getAttribute('data-profile-tab');
            document.getElementById(`profile-${tabId}-tab`).classList.add('active');
        });
    });
}

// New function for user-specific event listeners
function setupUserEventListeners() {
    // Add service button
    const addServiceBtn = document.querySelector('.user-services .btn-add');
    if (addServiceBtn) {
        addServiceBtn.addEventListener('click', () => {
            console.log('Add new service clicked - would show service creation form in production');
            // Here you would redirect to service creation page or open a modal
        });
    }
}

function loadAdminDashboard() {
    // Fetch platform statistics
    fetchPlatformStats();
    
    // Load user management data
    fetchUsersList();
    
    // Load revenue analytics
    fetchRevenueData();
    
    // Load recent orders across platform
    fetchAllOrders();
}

function loadUserDashboard() {
    // Load user's earnings data
    fetchUserEarnings();
    
    // Load user's orders
    fetchUserOrders();
    
    // Load user's services/gigs
    fetchUserServices();
    
    // Load marketplace activity for this user
    fetchUserActivity();
}

// Load user profile data
function loadUserProfileData() {
    // In a real app, you would fetch the user's profile data from the API
    // Here we're just using placeholder data
    document.getElementById('userProfileName').textContent = 'John Doe';
    document.getElementById('userProfileRole').textContent = 'Développeur Web';
    
    document.getElementById('userFirstName').value = 'John';
    document.getElementById('userLastName').value = 'Doe';
    document.getElementById('userEmail').value = 'john.doe@example.com';
    document.getElementById('userPhone').value = '+33 6 12 34 56 78';
    document.getElementById('userLocation').value = 'Paris, France';
    document.getElementById('userBio').value = 'Développeur web passionné avec 5 ans d\'expérience en création d\'applications web modernes et responsives.';
    
    // Set up profile form submission
    const personalInfoForm = document.getElementById('personalInfoForm');
    if (personalInfoForm) {
        personalInfoForm.addEventListener('submit', (e) => {
            e.preventDefault();
            
            // In a real app, you would send the form data to the API
            console.log('Saving profile data');
            
            // Show success message
            showToast('Profil mis à jour avec succès.');
        });
    }
}

// Existing functions remain unchanged
// ...existing code...

// Function to set up draggable dashboard cards
function setupDraggableCards() {
    // Get the current user for storing preferences
    const user = JSON.parse(localStorage.getItem('currentUser'));
    if (!user) return;
    
    // Add drag handles to all cards
    document.querySelectorAll('.card').forEach(card => {
        // Skip if already has a drag handle
        if (card.querySelector('.card-drag-handle')) return;
        
        // Create drag handle in the corner
        const dragHandle = document.createElement('div');
        dragHandle.className = 'card-drag-handle';
        dragHandle.title = "Drag to reorder";
        card.appendChild(dragHandle); // Add to end of card (will be positioned via CSS)
        
        // Prevent cards from being selected when clicking on them
        card.addEventListener('mousedown', function(e) {
            if (!e.target.classList.contains('card-drag-handle')) {
                e.stopPropagation(); // Don't propagate mousedown unless on handle
            }
        });
    });
    
    // Set up admin grid if admin user
    const adminGrid = document.getElementById('adminGrid');
    if (adminGrid) {
        // Load saved layout
        loadCardLayout(adminGrid, 'admin', user.email);
        
        // Initialize sortable with fixed settings
        new Sortable(adminGrid, {
            animation: 150,
            ghostClass: 'sortable-ghost',
            chosenClass: 'sortable-chosen',
            dragClass: 'sortable-drag',
            handle: '.card-drag-handle',
            forceFallback: true, // Force fallback for better cross-browser support
            fallbackClass: 'sortable-fallback',
            fallbackOnBody: true,
            swapThreshold: 0.65,
            onStart: function() {
                document.body.classList.add('dragging-active');
                console.log('Drag started');
            },
            onEnd: function() {
                document.body.classList.remove('dragging-active');
                console.log('Drag ended');
                saveCardLayout(adminGrid, 'admin', user.email);
            },
            onChoose: function(evt) {
                console.log('Card chosen', evt.item.dataset.cardId);
            }
        });
        
        // Admin reset layout button
        const resetBtn = document.getElementById('resetLayout');
        if (resetBtn) {
            resetBtn.addEventListener('click', () => {
                resetCardLayout(adminGrid, 'admin', user.email);
            });
        }
    }
    
    // Set up user grid if regular user
    const userGrid = document.getElementById('userGrid');
    if (userGrid) {
        // Load saved layout
        loadCardLayout(userGrid, 'user', user.email);
        
        // Initialize sortable with fixed settings
        new Sortable(userGrid, {
            animation: 150,
            ghostClass: 'sortable-ghost',
            chosenClass: 'sortable-chosen',
            dragClass: 'sortable-drag',
            handle: '.card-drag-handle',
            forceFallback: true, // Force fallback for better cross-browser support
            fallbackClass: 'sortable-fallback',
            fallbackOnBody: true,
            swapThreshold: 0.65,
            onStart: function() {
                document.body.classList.add('dragging-active');
                console.log('Drag started');
            },
            onEnd: function() {
                document.body.classList.remove('dragging-active');
                console.log('Drag ended');
                saveCardLayout(userGrid, 'user', user.email);
            },
            onChoose: function(evt) {
                console.log('Card chosen', evt.item.dataset.cardId);
            }
        });
        
        // User reset layout button
        const userResetBtn = document.getElementById('userResetLayout');
        if (userResetBtn) {
            userResetBtn.addEventListener('click', () => {
                resetCardLayout(userGrid, 'user', user.email);
            });
        }
    }
}

// Helper functions remain unchanged
// ...existing code...

function fetchPlatformStats() {
    // Simulate API call with sample data
    setTimeout(() => {
        document.getElementById('totalUsers').textContent = '1,245';
        document.getElementById('totalOrders').textContent = '3,892';
        document.getElementById('totalRevenue').textContent = '$128,459';
        document.getElementById('activeGigs').textContent = '567';
    }, 500);
}

function fetchUsersList() {
    // Simulate API call with sample data
    const usersTableBody = document.getElementById('usersTableBody');
    
    const sampleUsers = [
        { id: 1, name: 'John Doe', email: 'john@example.com', role: 'Seller', status: 'Active' },
        { id: 2, name: 'Jane Smith', email: 'jane@example.com', role: 'Buyer', status: 'Active' },
        { id: 3, name: 'Mark Wilson', email: 'mark@example.com', role: 'Both', status: 'Inactive' }
    ];
    
    usersTableBody.innerHTML = '';
    sampleUsers.forEach(user => {
        const row = document.createElement('tr');
        row.innerHTML = `
            <td>${user.id}</td>
            <td>${user.name}</td>
            <td>${user.email}</td>
            <td>${user.role}</td>
            <td><span class="status-badge ${user.status.toLowerCase()}">${user.status}</span></td>
            <td>
                <button class="action-btn"><i class="fas fa-edit"></i></button>
                <button class="action-btn"><i class="fas fa-trash"></i></button>
            </td>
        `;
        usersTableBody.appendChild(row);
    });
}

function fetchRevenueData() {
    // In a real application, this would fetch data from your backend
    // Here we're just setting up the chart
    
    // This is just a placeholder - in a real app you'd use a chart library like Chart.js
    const chartContainer = document.querySelector('.admin-revenue-chart .chart-container');
    chartContainer.innerHTML = `<div class="chart-placeholder">Revenue chart would render here</div>`;
}

function fetchAllOrders() {
    // Simulate API call with sample data
    const ordersContainer = document.querySelector('.admin-recent-orders .orders-list');
    
    const sampleOrders = [
        { id: '#ORD-5782', client: 'Michael Brown', service: 'Website Development', price: '$850', status: 'In Progress' },
        { id: '#ORD-5781', client: 'Sarah Williams', service: 'Logo Design', price: '$150', status: 'Completed' },
        { id: '#ORD-5780', client: 'James Taylor', service: 'SEO Optimization', price: '$450', status: 'Pending' }
    ];
    
    ordersContainer.innerHTML = '';
    sampleOrders.forEach(order => {
        const statusClass = order.status.toLowerCase().replace(' ', '-');
        const element = document.createElement('div');
        element.className = 'order-item';
        element.innerHTML = `
            <div class="order-id">${order.id}</div>
            <div class="order-info">
                <h4>${order.service}</h4>
                <p>Client: ${order.client}</p>
                <span class="badge ${statusClass}">${order.status}</span>
            </div>
            <div class="order-price">${order.price}</div>
        `;
        ordersContainer.appendChild(element);
    });
}

function fetchUserEarnings() {
    // Simulate API call with sample data for user dashboard
    document.querySelector('.user-earnings-value').textContent = '$2,459';
    document.querySelector('.user-orders-value').textContent = '12';
    document.querySelector('.user-rating-value').textContent = '4.8';
    document.querySelector('.user-clients-value').textContent = '64';
}

function fetchUserOrders() {
    // Similar to fetchAllOrders but for a specific user
    const ordersContainer = document.querySelector('.user-orders .orders-list');
    
    const sampleOrders = [
        { thumb: 'https://via.placeholder.com/50', service: 'Website Development', client: 'John Doe', price: '$850', status: 'In Progress' },
        { thumb: 'https://via.placeholder.com/50', service: 'Logo Design', client: 'Sarah Smith', price: '$150', status: 'Completed' }
    ];
    
    ordersContainer.innerHTML = '';
    sampleOrders.forEach(order => {
        const statusClass = order.status.toLowerCase().replace(' ', '-');
        const element = document.createElement('div');
        element.className = 'order-item';
        element.innerHTML = `
            <img src="${order.thumb}" alt="Order" class="order-thumb">
            <div class="order-info">
                <h4>${order.service}</h4>
                <p>Client: ${order.client}</p>
                <span class="badge ${statusClass}">${order.status}</span>
            </div>
            <div class="order-price">${order.price}</div>
        `;
        ordersContainer.appendChild(element);
    });
}

function fetchUserServices() {
    // Populate user services/gigs
    const servicesContainer = document.querySelector('.user-services .services-grid');
    
    const sampleServices = [
        { thumb: 'https://via.placeholder.com/100', title: 'Web Development', price: 'From $99', rating: '4.9', orders: '142' },
        { thumb: 'https://via.placeholder.com/100', title: 'Mobile App Design', price: 'From $149', rating: '4.7', orders: '87' }
    ];
    
    servicesContainer.innerHTML = '';
    sampleServices.forEach(service => {
        const element = document.createElement('div');
        element.className = 'service-item';
        element.innerHTML = `
            <img src="${service.thumb}" alt="Service">
            <h4>${service.title}</h4>
            <p>${service.price}</p>
            <div class="service-stats">
                <span><i class="fas fa-star"></i> ${service.rating}</span>
                <span><i class="fas fa-shopping-cart"></i> ${service.orders}</span>
            </div>
        `;
        servicesContainer.appendChild(element);
    });
}

function fetchUserActivity() {
    // Populate user activity feed
    const activityContainer = document.querySelector('.user-activity .activity-feed');
    
    const sampleActivities = [
        { icon: 'fas fa-comment-alt', text: 'New message from client', time: '2 minutes ago' },
        { icon: 'fas fa-heart', text: 'Your gig was saved by a client', time: '1 hour ago' }
    ];
    
    activityContainer.innerHTML = '';
    sampleActivities.forEach(activity => {
        const element = document.createElement('div');
        element.className = 'activity-item';
        element.innerHTML = `
            <i class="${activity.icon}"></i>
            <div class="activity-details">
                <p>${activity.text}</p>
                <span>${activity.time}</span>
            </div>
        `;
        activityContainer.appendChild(element);
    });
}

function performSearch(term, category = 'all') {
    const searchResults = document.querySelector('.search-results-container');
    
    // Simulate API search request
    setTimeout(() => {
        // In a real app, this would be a fetch request to your backend
        const results = [
            { type: 'service', title: 'Web Development', description: 'Professional web development services', icon: 'fas fa-laptop-code' },
            { type: 'user', title: 'John Smith', description: 'Web Developer', icon: 'fas fa-user' },
            { type: 'order', title: 'Order #1234', description: 'Mobile App Development', icon: 'fas fa-shopping-cart' }
        ];
        
        // Filter by category if not 'all'
        const filteredResults = category === 'all' 
            ? results 
            : results.filter(item => item.type === category.toLowerCase());
        
        if (filteredResults.length === 0) {
            searchResults.innerHTML = '<div class="no-results">No results found</div>';
            return;
        }
        
        // Display results
        searchResults.innerHTML = '';
        filteredResults.forEach(result => {
            const resultItem = document.createElement('div');
            resultItem.className = 'search-result-item';
            resultItem.innerHTML = `
                <div class="search-result-icon">
                    <i class="${result.icon}"></i>
                </div>
                <div class="search-result-content">
                    <h4>${result.title}</h4>
                    <p>${result.description}</p>
                </div>
            `;
            searchResults.appendChild(resultItem);
        });
    }, 300);
}

function loadNotifications() {
    const notificationsList = document.querySelector('.notifications-list');
    
    // Simulate API call to get notifications
    const sampleNotifications = [
        { 
            type: 'message', 
            title: 'New message', 
            text: 'You have a new message from John Doe',
            time: '5 minutes ago',
            unread: true
        },
        { 
            type: 'order', 
            title: 'Order completed', 
            text: 'Your order #1234 has been completed',
            time: '2 hours ago',
            unread: true
        },
        { 
            type: 'alert', 
            title: 'System notification', 
            text: 'Your account was successfully verified',
            time: '1 day ago',
            unread: false
        }
    ];
    
    notificationsList.innerHTML = '';
    sampleNotifications.forEach(notification => {
        const element = document.createElement('div');
        element.className = `notification-item ${notification.unread ? 'unread' : ''}`;
        element.innerHTML = `
            <div class="notification-icon ${notification.type}">
                <i class="${getNotificationIcon(notification.type)}"></i>
            </div>
            <div class="notification-content">
                <h4 class="notification-title">${notification.title}</h4>
                <p class="notification-text">${notification.text}</p>
                <span class="notification-time">${notification.time}</span>
            </div>
        `;
        notificationsList.appendChild(element);
    });
}

function getNotificationIcon(type) {
    switch(type) {
        case 'message': return 'fas fa-envelope';
        case 'order': return 'fas fa-shopping-bag';
        case 'alert': return 'fas fa-bell';
        default: return 'fas fa-bell';
    }
}

// Function to set up draggable dashboard cards
function setupDraggableCards() {
    // Get the current user for storing preferences
    const user = JSON.parse(localStorage.getItem('currentUser'));
    if (!user) return;
    
    // Add drag handles to all cards
    document.querySelectorAll('.card').forEach(card => {
        // Skip if already has a drag handle
        if (card.querySelector('.card-drag-handle')) return;
        
        // Create drag handle in the corner
        const dragHandle = document.createElement('div');
        dragHandle.className = 'card-drag-handle';
        dragHandle.title = "Drag to reorder";
        card.appendChild(dragHandle); // Add to end of card (will be positioned via CSS)
        
        // Prevent cards from being selected when clicking on them
        card.addEventListener('mousedown', function(e) {
            if (!e.target.classList.contains('card-drag-handle')) {
                e.stopPropagation(); // Don't propagate mousedown unless on handle
            }
        });
    });
    
    // Set up admin grid if admin user
    const adminGrid = document.getElementById('adminGrid');
    if (adminGrid) {
        // Load saved layout
        loadCardLayout(adminGrid, 'admin', user.email);
        
        // Initialize sortable with fixed settings
        new Sortable(adminGrid, {
            animation: 150,
            ghostClass: 'sortable-ghost',
            chosenClass: 'sortable-chosen',
            dragClass: 'sortable-drag',
            handle: '.card-drag-handle',
            forceFallback: true, // Force fallback for better cross-browser support
            fallbackClass: 'sortable-fallback',
            fallbackOnBody: true,
            swapThreshold: 0.65,
            onStart: function() {
                document.body.classList.add('dragging-active');
                console.log('Drag started');
            },
            onEnd: function() {
                document.body.classList.remove('dragging-active');
                console.log('Drag ended');
                saveCardLayout(adminGrid, 'admin', user.email);
            },
            onChoose: function(evt) {
                console.log('Card chosen', evt.item.dataset.cardId);
            }
        });
        
        // Admin reset layout button
        const resetBtn = document.getElementById('resetLayout');
        if (resetBtn) {
            resetBtn.addEventListener('click', () => {
                resetCardLayout(adminGrid, 'admin', user.email);
            });
        }
    }
    
    // Set up user grid if regular user
    const userGrid = document.getElementById('userGrid');
    if (userGrid) {
        // Load saved layout
        loadCardLayout(userGrid, 'user', user.email);
        
        // Initialize sortable with fixed settings
        new Sortable(userGrid, {
            animation: 150,
            ghostClass: 'sortable-ghost',
            chosenClass: 'sortable-chosen',
            dragClass: 'sortable-drag',
            handle: '.card-drag-handle',
            forceFallback: true, // Force fallback for better cross-browser support
            fallbackClass: 'sortable-fallback',
            fallbackOnBody: true,
            swapThreshold: 0.65,
            onStart: function() {
                document.body.classList.add('dragging-active');
                console.log('Drag started');
            },
            onEnd: function() {
                document.body.classList.remove('dragging-active');
                console.log('Drag ended');
                saveCardLayout(userGrid, 'user', user.email);
            },
            onChoose: function(evt) {
                console.log('Card chosen', evt.item.dataset.cardId);
            }
        });
        
        // User reset layout button
        const userResetBtn = document.getElementById('userResetLayout');
        if (userResetBtn) {
            userResetBtn.addEventListener('click', () => {
                resetCardLayout(userGrid, 'user', user.email);
            });
        }
    }
}

// Save the current card layout to localStorage
function saveCardLayout(container, role, userEmail) {
    if (!container) return;
    
    // Get all cards and their IDs in the current order
    const cards = container.querySelectorAll('[data-card-id]');
    const layout = Array.from(cards).map(card => card.dataset.cardId);
    
    // Create a layout key specific to this user and role
    const layoutKey = `dashboard-layout-${role}-${userEmail}`;
    
    // Save to localStorage
    localStorage.setItem(layoutKey, JSON.stringify(layout));
    console.log(`Layout saved for ${role} dashboard:`, layout);
    
    // Show a brief notification
    showToast('Dashboard layout saved');
}

// Load saved card layout from localStorage
function loadCardLayout(container, role, userEmail) {
    if (!container) return;
    
    // Create a layout key specific to this user and role
    const layoutKey = `dashboard-layout-${role}-${userEmail}`;
    
    // Try to get saved layout
    const savedLayout = localStorage.getItem(layoutKey);
    
    if (!savedLayout) {
        console.log(`No saved layout found for ${role} dashboard. Using default.`);
        return;
    }
    
    try {
        const layout = JSON.parse(savedLayout);
        
        // Rearrange cards according to saved layout
        layout.forEach(cardId => {
            const card = container.querySelector(`[data-card-id="${cardId}"]`);
            if (card) {
                container.appendChild(card); // Move to the end in the correct order
            }
        });
        
        console.log(`Layout loaded for ${role} dashboard:`, layout);
    } catch (error) {
        console.error('Error loading dashboard layout:', error);
    }
}

// Reset to default layout
function resetCardLayout(container, role, userEmail) {
    if (!container) return;
    
    // Remove saved layout
    const layoutKey = `dashboard-layout-${role}-${userEmail}`;
    localStorage.removeItem(layoutKey);
    
    // Reload the page to restore default layout
    location.reload();
}

// Simple toast notification
function showToast(message) {
    // Create toast element if it doesn't exist
    let toast = document.getElementById('dashboard-toast');
    if (!toast) {
        toast = document.createElement('div');
        toast.id = 'dashboard-toast';
        toast.className = 'toast-notification';
        document.body.appendChild(toast);
    }
    
    // Add message and show
    toast.textContent = message;
    toast.classList.add('show');
    
    // Hide after 3 seconds
    setTimeout(() => {
        toast.classList.remove('show');
    }, 3000);
}

// Add global style to prevent text selection during drag
function preventTextSelectionDuringDrag() {
    const style = document.createElement('style');
    style.innerHTML = `
        body.dragging-active {
            cursor: grabbing !important;
        }
        
        body.dragging-active * {
            user-select: none !important;
            -webkit-user-select: none !important;
            -moz-user-select: none !important;
        }
        
        .sortable-fallback {
            transform: rotate(2deg);
            box-shadow: 0 20px 40px rgba(0,0,0,0.15) !important;
        }
    `;
    document.head.appendChild(style);
}
