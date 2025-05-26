<div id="layoutSidenav_nav">
    <nav class="sb-sidenav accordion sb-sidenav-soft" id="sidenavAccordion">
        <div class="sb-sidenav-menu">
            <div class="nav">
                <div class="sb-sidenav-menu-heading">Core</div>
                <a class="nav-link" href="index.php">
                    <div class="sb-nav-link-icon"><i class="fas fa-tachometer-alt"></i></div>
                    Dashboard
                </a>
                <a class="nav-link" href="product_management.php">
                    <div class="sb-nav-link-icon"><i class="fas fa-box"></i></div>
                    Product
                </a>
                <a class="nav-link" href="category_management.php">
                    <div class="sb-nav-link-icon"><i class="fas fa-tags"></i></div>
                    Categories
                </a>
                <a class="nav-link" href="../admin/user_management.php">
                    <div class="sb-nav-link-icon"><i class="fas fa-users"></i></div>
                    Users
                </a>

                <div class="sb-sidenav-menu-heading">Interface</div>
                <a class="nav-link collapsed" href="#" data-bs-toggle="collapse" data-bs-target="#collapseLayouts" aria-expanded="false" aria-controls="collapseLayouts">
                    <div class="sb-nav-link-icon"><i class="fas fa-columns"></i></div>
                    Layouts
                    <div class="sb-sidenav-collapse-arrow"><i class="fas fa-angle-down"></i></div>
                </a>
                <div class="collapse" id="collapseLayouts" aria-labelledby="headingOne" data-bs-parent="#sidenavAccordion">
                    <nav class="sb-sidenav-menu-nested nav">
                        <a class="nav-link" href="layout-static.html">Static Navigation</a>
                        <a class="nav-link" href="layout-sidenav-light.html">Light Sidenav</a>
                    </nav>
                </div>
                <a class="nav-link collapsed" href="#" data-bs-toggle="collapse" data-bs-target="#collapsePages" aria-expanded="false" aria-controls="collapsePages">
                    <div class="sb-nav-link-icon"><i class="fas fa-book-open"></i></div>
                    Pages
                    <div class="sb-sidenav-collapse-arrow"><i class="fas fa-angle-down"></i></div>
                </a>
                <div class="collapse" id="collapsePages" aria-labelledby="headingTwo" data-bs-parent="#sidenavAccordion">
                    <nav class="sb-sidenav-menu-nested nav accordion" id="sidenavAccordionPages">
                        <a class="nav-link collapsed" href="#" data-bs-toggle="collapse" data-bs-target="#pagesCollapseAuth" aria-expanded="false" aria-controls="pagesCollapseAuth">
                            Authentication
                            <div class="sb-sidenav-collapse-arrow"><i class="fas fa-angle-down"></i></div>
                        </a>
                        <div class="collapse" id="pagesCollapseAuth" aria-labelledby="headingOne" data-bs-parent="#sidenavAccordionPages">
                            <nav class="sb-sidenav-menu-nested nav">
                                <a class="nav-link" href="login.html">Login</a>
                                <a class="nav-link" href="register.html">Register</a>
                                <a class="nav-link" href="password.html">Forgot Password</a>
                            </nav>
                        </div>
                        <a class="nav-link collapsed" href="#" data-bs-toggle="collapse" data-bs-target="#pagesCollapseError" aria-expanded="false" aria-controls="pagesCollapseError">
                            Error
                            <div class="sb-sidenav-collapse-arrow"><i class="fas fa-angle-down"></i></div>
                        </a>
                        <div class="collapse" id="pagesCollapseError" aria-labelledby="headingOne" data-bs-parent="#sidenavAccordionPages">
                            <nav class="sb-sidenav-menu-nested nav">
                                <a class="nav-link" href="401.html">401 Page</a>
                                <a class="nav-link" href="404.html">404 Page</a>
                                <a class="nav-link" href="500.html">500 Page</a>
                            </nav>
                        </div>
                    </nav>
                </div>
            </div>
        </div>
        <div class="sb-sidenav-footer">
            <div class="small">Logged in as:</div>
            <?php 
                if(isset($_SESSION['full_name'])) {
                    echo $_SESSION['full_name'];
                    if(isset($_SESSION['role'])) {
                        echo " (" . ucfirst($_SESSION['role']) . ")";
                    }
                } else {
                    echo "Guest";
                }
            ?>
        </div>
    </nav>
</div>

<style>
/* Soft Color Palette */
:root {
    --sidebar-bg: #f8f9fa;
    --sidebar-text: #495057;
    --sidebar-hover: #e9ecef;
    --sidebar-active: #dee2e6;
    --sidebar-icon: #6c757d;
    --sidebar-heading: #adb5bd;
    --sidebar-border: #e0e0e0;
    --sidebar-footer: #e9ecef;
}

/* Sidebar Styling */
.sb-sidenav-soft {
    background-color: var(--sidebar-bg);
    color: var(--sidebar-text);
    border-right: 1px solid var(--sidebar-border);
}

.sb-sidenav-soft .sb-sidenav-menu .nav-link {
    color: var(--sidebar-text);
    transition: all 0.2s ease;
}

.sb-sidenav-soft .sb-sidenav-menu .nav-link:hover {
    color: var(--sidebar-text);
    background-color: var(--sidebar-hover);
}

.sb-sidenav-soft .sb-sidenav-menu .nav-link .sb-nav-link-icon {
    color: var(--sidebar-icon);
}

.sb-sidenav-soft .sb-sidenav-menu .nav-link.active {
    background-color: var(--sidebar-active);
    font-weight: 500;
}

.sb-sidenav-soft .sb-sidenav-menu-heading {
    color: var(--sidebar-heading);
    font-size: 0.75rem;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.sb-sidenav-soft .sb-sidenav-footer {
    background-color: var(--sidebar-footer);
    color: var(--sidebar-text);
    border-top: 1px solid var(--sidebar-border);
    font-size: 0.9rem;
}

.sb-sidenav-soft .sb-sidenav-collapse-arrow {
    color: var(--sidebar-icon);
}

/* Collapsed menu items */
.sb-sidenav-soft .sb-sidenav-menu-nested .nav-link {
    color: var(--sidebar-text);
    padding-left: 2.5rem;
}

.sb-sidenav-soft .sb-sidenav-menu-nested .nav-link:hover {
    background-color: var(--sidebar-hover);
}

/* Active state for parent items when dropdown is open */
.sb-sidenav-soft .nav-link.collapsed:hover {
    background-color: var(--sidebar-hover);
}

/* Responsive adjustments */
@media (min-width: 992px) {
    .sb-sidenav-soft {
        width: 225px;
    }
}
</style>