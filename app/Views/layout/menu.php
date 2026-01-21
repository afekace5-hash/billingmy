<li class="nav-item">
    <a class="nav-link menu-link" href="<?= site_url('dashboard') ?>" aria-expanded="false">
        <i class="bx bx-home-circle"></i> <span data-key="t-dashboards">Dashboards</span>
    </a>
</li> <!-- end Dashboard Menu -->
<!-- <li class="nav-item">
    <a class="nav-link menu-link collapsed" href="#sidebarAddons" data-bs-toggle="collapse" role="button" aria-expanded="false" aria-controls="sidebarAddons">
        <i class="bx bx-extension"></i> <span data-key="t-addons">Addon</span>
        <i class="bx bx-chevron-right submenu-indicator"></i>
    </a>
    <div class="collapse menu-dropdown" id="sidebarAddons">
        <ul class="nav nav-sm flex-column">
            <li class="nav-item">
                <a href="<?= site_url('addons/geniacs') ?>" class="nav-link" data-key="t-geniacs">Geniacs</a>
            </li>
            <li class="nav-item">
                <a href="<?= site_url('addons/vpn-remote') ?>" class="nav-link" data-key="t-vpn-remote">AddOn VPN Remote</a>
            </li>
        </ul>
    </div>
</li> -->

<li class="sidebar-main-title">
    <div>
        <h6>Apps</h6>
    </div>
</li>

<li class="nav-item">
    <a class="nav-link menu-link" href="<?= site_url('customers') ?>">
        <i class="bx bx-user"></i> <span>Customers</span>
    </a>
</li>
<li class="nav-item">
    <a class="nav-link menu-link" href="<?= site_url('customer-register') ?>">
        <i class="bx bx-user-plus"></i> <span>Cust Register</span>
    </a>
</li>
<li class="nav-item">
    <a class="nav-link menu-link collapsed" href="#sidebarInstallation" data-bs-toggle="collapse" role="button" aria-expanded="false" aria-controls="sidebarInstallation">
        <i class="bx bx-wifi"></i> <span data-key="t-installation">Installation</span>
        <i class="bx bx-chevron-right submenu-indicator"></i>
    </a>
    <div class="collapse menu-dropdown" id="sidebarInstallation">
        <ul class="nav nav-sm flex-column">
            <li class="nav-item">
                <a href="<?= site_url('installation/waiting-list') ?>" class="nav-link" data-key="t-waiting-list">Waiting List</a>
            </li>
            <li class="nav-item">
                <a href="<?= site_url('installation/on-progress') ?>" class="nav-link" data-key="t-on-progress">On Progress</a>
            </li>
            <li class="nav-item">
                <a href="<?= site_url('installation/history') ?>" class="nav-link" data-key="t-vpn-remote">History</a>
            </li>
        </ul>
    </div>
</li>
<li class="nav-item">
    <a class="nav-link menu-link" href="<?= site_url('ticket') ?>" aria-expanded="false" aria-controls="sidebarPages">
        <i class="bx bx-discussion"></i> <span data-key="t-pages">Tikets</span>
    </a>
</li>
<li class="nav-item">
    <a class="nav-link menu-link" href="<?= site_url('remote-access') ?>" aria-expanded="false" aria-controls="sidebarPages">
        <i class='bx  bx-compare'></i> <span data-key="t-pages">Remote Access</span>
    </a>
</li>
<li class="nav-item">
    <a class="nav-link menu-link" href="<?= site_url('clustering') ?>" aria-expanded="false" aria-controls="sidebarPages">
        <i class="bx bx-map-pin"></i> <span data-key="t-pages">Coverage</span>
    </a>
</li>

<li class="nav-item">
    <a class="nav-link menu-link" href="<?= site_url('biaya_tambahan') ?>" aria-expanded="false" aria-controls="sidebarPages">
        <i class='bx  bx-dollar'></i> <span data-key="t-pages">Diskon</span>
    </a>
</li>

<li class="nav-item">
    <a class="nav-link menu-link" href="<?= site_url('prorate') ?>" aria-expanded="false" aria-controls="sidebarPages">
        <i class='bx  bx-dollar'></i> <span data-key="t-pages">Prorate</span>
    </a>
</li>
<li class="nav-item">
    <a class="nav-link menu-link" href="<?= site_url('invoice') ?>" aria-expanded="false" aria-controls="sidebarPages">
        <i class='bx bx-file'></i> <span data-key="t-pages">Invoice</span>
    </a>
</li>
<li class="nav-item">
    <a class="nav-link menu-link" href="<?= site_url('users') ?>" aria-expanded="false" aria-controls="sidebarPages">
        <i class='bx bx-user'></i> <span data-key="t-pages">Users</span>
    </a>
</li>
<li class="sidebar-main-title">
    <div>
        <h6>REPORT</h6>
    </div>
</li>

<li class="nav-item">
    <a class="nav-link menu-link collapsed" href="#sidebarTransaction" data-bs-toggle="collapse" role="button" aria-expanded="false" aria-controls="sidebarTransaction">
        <i class='bx  bx-law'></i> <span data-key="t-transaction">Transaction</span>
        <i class="bx bx-chevron-right submenu-indicator"></i>
    </a>
    <div class="collapse menu-dropdown" id="sidebarTransaction">
        <ul class="nav nav-sm flex-column">
            <li class="nav-item">
                <a href="<?= site_url('transaction/transaction') ?>" class="nav-link" data-key="t-mutasi-keuangan">Transaction</a>
            </li>
            <li class="nav-item">
                <a href="<?= site_url('transaction/operational-cost') ?>" class="nav-link" data-key="t-mutasi-keuangan">Operasional Cost</a>
            </li>
            <li class="nav-item">
                <a href="<?= site_url('transaction/invoices') ?>" class="nav-link" data-key="t-pembayaran-online">Invoice</a>
            </li>
        </ul>
    </div>
</li>
<li class="sidebar-main-title">
    <div>
        <h6>INVENTORY</h6>
    </div>
</li>

<li class="nav-item">
    <a class="nav-link menu-link collapsed" href="#sidebarInventory" data-bs-toggle="collapse" role="button" aria-expanded="false" aria-controls="sidebarInventory">
        <i class='bx  bx-inbox'></i> <span data-key="t-inventory">Inventory</span>
        <i class="bx bx-chevron-right submenu-indicator"></i>
    </a>
    <div class="collapse menu-dropdown" id="sidebarInventory">
        <ul class="nav nav-sm flex-column">
            <li class="nav-item">
                <a href="<?= site_url('inventory/inventory') ?>" class="nav-link" data-key="t-mutasi-keuangan">Inventory</a>
            </li>
            <li class="nav-item">
                <a href="<?= site_url('inventory/master-item') ?>" class="nav-link" data-key="t-mutasi-keuangan">Master Item</a>
            </li>
            <li class="nav-item">
                <a href="<?= site_url('inventory/configuration') ?>" class="nav-link" data-key="t-pembayaran-online">Configuration</a>
            </li>
        </ul>
    </div>
</li>
<li class="sidebar-main-title">
    <div>
        <h6>NOTIFICATIONS & BROADCAST</h6>
    </div>
</li>
<li class="nav-item">
    <a href="<?= site_url('notification') ?>" class="nav-link menu-link" data-key="t-whatsapp-settings" aria-expanded="false" aria-controls="sidebarPages">
        <i class='bx  bx-bell-ring'></i> <span data-key=" t-pages">Notification</span>
    </a>
</li>
<li class="nav-item">
    <a href="<?= site_url('whatsapp/broadcast') ?>" class="nav-link menu-link" data-key="t-whatsapp-settings" aria-expanded="false" aria-controls="sidebarPages">
        <i class='bx  bx-megaphone'></i> <span data-key=" t-pages">Broadcast</span>
    </a>
</li>
<li class="nav-item">
    <a href="<?= site_url('whatsapp/account') ?>" class="nav-link menu-link" data-key="t-whatsapp-settings" aria-expanded="false" aria-controls="sidebarPages">
        <i class="bx bxl-whatsapp"></i> <span data-key=" t-pages">Account</span>
    </a>
</li>
<li class="sidebar-main-title">
    <div>
        <h6>WITHDRAW</h6>
    </div>
</li>
<li class="nav-item">
    <a class="nav-link menu-link" href="<?= site_url('withdraw') ?>" aria-expanded="false" aria-controls="sidebarPages">
        <i class='bx bx-dollar'></i> <span data-key="t-pages">Withdraw Req</span>
    </a>
</li>
<li class="sidebar-main-title">
    <div>
        <h6>Mikrotik integration</h6>
    </div>
</li>

<li class="nav-item">
    <a class="nav-link menu-link" href="<?= site_url('router-os-conf') ?>" aria-expanded="false" aria-controls="sidebarPages">
        <i class='bx  bx-robot'></i> <span data-key="t-pages">RouterOS Conf</span>
    </a>
</li>
<li class="nav-item">
    <a class="nav-link menu-link" href="<?= site_url('pppoe-accounts') ?>" aria-expanded="false" aria-controls="sidebarPages">
        <i class='bx bx-network-chart'></i> <span data-key="t-pages">PPPoE Accounts</span>
    </a>
</li>
<li class="sidebar-main-title">
    <div>
        <h6>SETTINGS</h6>
    </div>
</li>
<li class="nav-item">
    <a class="nav-link menu-link collapsed" href="#sidebarMasterData" data-bs-toggle="collapse" role="button" aria-expanded="false" aria-controls="sidebarMasterData">
        <i class="bx bx-cog"></i> <span data-key="t-master">Master Data</span>
        <i class="bx bx-chevron-right submenu-indicator"></i>
    </a>
    <div class="collapse menu-dropdown" id="sidebarMasterData">
        <ul class="nav nav-sm flex-column">
            <li class="nav-item">
                <a href="<?= site_url('internet-packages/package-profile') ?>" class="nav-link" data-key="t-master">Product</a>
            </li>
            <li class="nav-item">
                <a href="<?= site_url('master/notification-whatsapp') ?>" class="nav-link" data-key="t-master">Notifikasi WhatsApp</a>
            </li>
            <li class="nav-item">
                <a href="<?= site_url('settings/branch') ?>" class="nav-link" data-key="t-master">Branch</a>
            </li>
            <li class="nav-item">
                <a href="<?= site_url('master/hotspot-profile') ?>" class="nav-link" data-key="t-master">Hotspot Profile</a>
            </li>
            <li class="nav-item">
                <a href="<?= site_url('master/area') ?>" class="nav-link" data-key="t-master">Area</a>
            </li>
            <li class="nav-item">
                <a href="<?= site_url('master/odp') ?>" class="nav-link" data-key="t-master">ODP</a>
            </li>
            <li class="nav-item">
                <a href="<?= site_url('master/promos') ?>" class="nav-link" data-key="t-master">Promo</a>
            </li>
            <li class="nav-item">
                <a href="<?= site_url('master/promo-type') ?>" class="nav-link" data-key="t-master">Promo Type</a>
            </li>
            <li class="nav-item">
                <a href="<?= site_url('master/payment-method') ?>" class="nav-link" data-key="t-master">Payment Method</a>
            </li>
            <li class="nav-item">
                <a href="<?= site_url('admin/transaction_category') ?>" class="nav-link" data-key="t-master">Transaction Category</a>
            </li>
            <li class="nav-item">
                <a href="<?= site_url('admin/ticket_category') ?>" class="nav-link" data-key="t-master">Ticket Category</a>
            </li>
            <li class="nav-item">
                <a href="<?= site_url('admin/router_customer') ?>" class="nav-link" data-key="t-master">Router Customer</a>
            </li>
            <li class="nav-item">
                <a href="<?= site_url('settings/company') ?>" class="nav-link" data-key="t-company">Perusahaan</a>
            </li>
            <li class="nav-item">
                <a href="<?= site_url('settings/master-bank') ?>" class="nav-link" data-key="t-applications">Master Bank</a>
            </li>
            <li class="nav-item">
                <a href="<?= site_url('settings/payment-getway') ?>" class="nav-link" data-key="t-applications">Payment Getway</a>
            </li>
        </ul>
    </div>
</li>
<li class="nav-item">
    <a class="nav-link menu-link" href="<?= site_url('setting') ?>" aria-expanded="false" aria-controls="sidebarPages">
        <i class='bx bx-cog'></i> <span data-key="t-pages">Settings</span>
    </a>
</li>
<li class="nav-item">
    <a class="nav-link menu-link" href="<?= site_url('log-activity') ?>" aria-expanded="false" aria-controls="sidebarPages">
        <i class='bx  bx-browser-activity'></i> <span data-key="t-pages">Log Activity</span>
    </a>
</li>