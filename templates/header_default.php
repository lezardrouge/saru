<?php
/**
 * SARU
 * organize your contacts
 *
 * Copyright (c) 2012-2018 Marie Kuntz - Lezard Rouge
 *
 * This file is part of SARU.
 * SARU is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as
 * published by the Free Software Foundation, either version 3 of the
 * License, or (at your option) any later version.
 *
 * SARU is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Affero General Public License for more details.

 * You should have received a copy of the GNU Affero General Public License
 * along with SARU. If not, see <http://www.gnu.org/licenses/>.
 * See LICENSE.TXT file for more information.
 *
 * Saru is released under dual license, AGPL and commercial license.
 * If you need a commercial license or if you don't know which licence you need,
 * please contact us at <info@saru.fr>
 *
 */

/**
 * TEMPLATE
 * header (default template)
 *
 * @since	1.0
 * @author	Marie Kuntz / Lézard Rouge <mariek@lezard-rouge.fr>
 */
?>
<!DOCTYPE html>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title>Saru</title>

<link href="bootstrap/css/bootstrap.min.css" rel="stylesheet" media="screen">
<link href="bootstrap/css/bootstrap-responsive.css" rel="stylesheet">
<link type="text/css" media="all" rel="stylesheet" href="css/layout.css">
<link type="text/css" media="all" rel="stylesheet" href="css/elements.css">
<link type="text/css" media="all" rel="stylesheet" href="css/jquery-ui.start.css">

<link rel="icon" type="image/png" href="favicon.png" />
<!--[if IE]><link rel="shortcut icon" type="image/x-icon" href="favicon.ico" /><![endif]-->

<script type="text/javascript" src="inc/functions.js"></script>
<script type="text/javascript" src="js/jquery-1.7.2.min.js"></script>
<script type="text/javascript" src="js/jquery-ui-1.10.2.custom.min.js"></script>
<script src="bootstrap/js/bootstrap.min.js"></script>
<script type="text/javascript">
	// break potential frames
	if (top != self) {
		top.location.href = '<?php echo URL_PATH; ?>';
	}
	$(function() {
		$('.alert-success').delay(3000).animate({
			opacity: 0,
			height:  0,
			margin:  0,
			padding: 0,
			border:  0
		}, 600);
	});
</script>
<?php if(isset($scripts)): echo $scripts; endif; ?>

</head>

<body>
<div class="container-fluid">

	<div class="row">
<!-- ---------------------------------------------------------------------------
	menu -->
	<div class="span10">
<?php if($session->getSessionData('user_id') !== false): ?>
		<ul class="nav nav-pills">
			<li<?php if(isset($current) && $current == 'home'): ?> class="active"<?php endif; ?>><a href="index.php"><img src="images/home.png" alt="[]" class="icon"></a></li>

	<!-- contacts & companies -->
	<?php if(Access::userCanAccess('contact_list', false) OR Access::userCanAccess('company_list', false)): ?>
			<li class="dropdown<?php if(isset($current) && $current == 'contact'): ?> active<?php endif; ?>">
				<a href="#" class="dropdown-toogle" data-toggle="dropdown"><img src="images/contact.png" alt="[]" class="icon">Annuaire<b class="caret"></b></a>
				<ul class="dropdown-menu">
			<?php if(Access::userCanAccess('contact_list', false)): ?>
					<li <?php if(isset($current) && $current == 'contact'): ?> class="active"<?php endif; ?>>
						<a href="contacts.php"><img src="images/contact.png" alt="[]" class="icon">Liste des contacts</a>
					</li>
			<?php endif; ?>
			<?php if(Access::userCanAccess('contact_form', false)): ?>
					<li<?php if(isset($current) && $current == 'new_contact'): ?> class="active"<?php endif; ?>>
						<a href="contacts.php?tpl=form&contact_id=0"><img src="images/add.png" alt="+" class="icon">Nouveau contact</a>
					</li>
			<?php endif; ?>
			<?php if(Access::userCanAccess('company_list', false)): ?>
					<li <?php if(isset($current) && $current == 'company'): ?> class="active"<?php endif; ?>>
						<a href="companies.php"><img src="images/company.png" alt="[]" class="icon">Liste des entreprises</a>
					</li>
			<?php endif; ?>
			<?php if(Access::userCanAccess('company_form', false)): ?>
					<li<?php if(isset($current) && $current == 'new_company'): ?> class="active"<?php endif; ?>>
						<a href="companies.php?tpl=form&contact_id=0"><img src="images/company_add.png" alt="+" class="icon">Nouvelle entreprise</a>
					</li>
			<?php endif; ?>
			<?php if(Access::userCanAccess('contact_import', false)): ?>
					<li<?php if(isset($current) && $current == 'contact_import'): ?> class="active"<?php endif; ?>>
						<a href="import_export.php?tpl=import_contacts"><img src="images/file_upload.png" alt="[]" class="icon">Importer des contacts</a>
					</li>
			<?php endif; ?>
			<?php if(Access::userCanAccess('contact_export', false)): ?>
					<li<?php if(isset($current) && $current == 'contact_export'): ?> class="active"<?php endif; ?>>
						<a href="import_export.php?tpl=export_contacts"><img src="images/file_download.png" alt="[]" class="icon">Exporter les contacts</a>
					</li>
			<?php endif; ?>
			<?php if(Access::userCanAccess('contact_import', false) || Access::userCanAccess('contact_export', false)): ?>
					<li<?php if(isset($current) && $current == 'help'): ?> class="active"<?php endif; ?>>
						<a href="index.php?tpl=help"><img src="images/information.png" alt="[]" class="icon">Aide pour l'import/export</a>
					</li>
			<?php endif; ?>
				</ul>
			</li>
	<?php endif; ?>

	<!-- meetings -->
	<?php if(Access::userCanAccess('meeting_list', false)): ?>
			<li class="dropdown<?php if(isset($current) && $current == 'meeting'): ?> active<?php endif; ?>">
				<a href="#" class="dropdown-toogle" data-toggle="dropdown"><img src="images/calendar.png" alt="[]" class="icon"><?php echo Utils::mb_ucfirst($lang->meetings); ?><b class="caret"></b></a>
				<ul class="dropdown-menu">
			<?php if(Access::userCanAccess('meeting_list', false)): ?>
					<li<?php if(isset($current) && $current == 'meeting'): ?> class="active"<?php endif; ?>><a href="meetings.php"><img src="images/calendar.png" alt="[]" class="icon">Liste</a></li>
			<?php endif; ?>
			<?php if(Access::userCanAccess('meeting_form', false)): ?>
					<li<?php if(isset($current) && $current == 'new_meeting'): ?> class="active"<?php endif; ?>><a href="meetings.php?tpl=form&meeting_id=0"><img src="images/add.png" alt="+" class="icon"><?php echo Utils::mb_ucfirst($lang->new_meeting); ?></a></li>
			<?php endif; ?>
			<?php if(Access::userCanAccess('meeting_export', false)): ?>
					<li<?php if(isset($current) && $current == 'meeting_export'): ?> class="active"<?php endif; ?>>
						<a href="import_export.php?tpl=export_history"><img src="images/file_download.png" alt="[]" class="icon"><?php echo Utils::mb_ucfirst($lang->export_meeting); ?></a>
					</li>
			<?php endif; ?>
				</ul>
			</li>
	<?php endif; ?>

	<!-- alerts -->
	<?php if(Access::userCanAccess('alert_list', false)): ?>
		<li class="dropdown<?php if(isset($current) && $current == 'alert'): ?> active<?php endif; ?>">
				<a href="#" class="dropdown-toogle" data-toggle="dropdown"><img src="images/alerte.png" alt="[]" class="icon">Alertes<b class="caret"></b></a>
				<ul class="dropdown-menu">
			<?php if(Access::userCanAccess('alert_list', false)): ?>
					<li<?php if(isset($current) && $current == 'alert'): ?> class="active"<?php endif; ?>><a href="alerts.php"><img src="images/alerte.png" alt="[]" class="icon">Liste</a></li>
			<?php endif; ?>
			<?php if(Access::userCanAccess('alert_form', false)): ?>
					<li<?php if(isset($current) && $current == 'new_meeting'): ?> class="active"<?php endif; ?>><a href="alerts.php?tpl=form&alert_id=0"><img src="images/add.png" alt="+" class="icon">Nouvelle alerte</a></li>
			<?php endif; ?>
				</ul>
			</li>
	<?php endif; ?>

	<!-- accounts -->
	<?php if(MOD_ACCOUNT == 1): ?>
		<?php if(Access::userCanAccess('account_list', false)): ?>
			<li<?php if(isset($current) && $current == 'account'): ?> class="active"<?php endif; ?>><a href="accounts.php"><img src="images/contact.png" alt="[]" class="icon"><?php echo Utils::mb_ucfirst($lang->accounts); ?></a></li>
		<?php endif; ?>
	<?php endif; ?>

	<?php if(Access::userCanAccess('admin', false)): ?>
			<li<?php if(isset($current) && $current == 'admin'): ?> class="active"<?php endif; ?>><a href="index.php?tpl=admin"><img src="images/admin.png" alt="*" class="icon">Admin</a></li>
	<?php endif; ?>

	<?php if(Access::userCanAccess('contact_list', false) OR Access::userCanAccess('company_list', false)): ?>
			<li><form class="navbar-search pull-left form-search">
					<input type="text" class="search-query input-medium" placeholder="Recherche rapide" autocomplete="off" id="quicksearch">
				</form>
			</li>
	<?php endif; ?>

	<?php if(MOD_ACCESS === 1): ?>
			<li class="pull-right"><a href="login.php?action=logout&token=<?php echo $session->getSessionData('token'); ?>"><img src="images/logout.png" alt="[->]" class="icon"></a></li>
	<?php endif; ?>
		</ul>
<?php endif; ?>
	</div><!-- .span10 -->

	<div id="logo" class="span2"><img src="images/logo_saru.png" alt="logo"></div>

	</div><!-- .row -->

<?php if($session->getSessionData('user_id') !== false
		&& $session->getSessionData('account_name') !== false): ?>
	<!-- ---------------------------------------------------------------------------
	account name -->
	<div class="account-name"><?php echo $session->getSessionData('account_name'); ?></div>
<?php endif; ?>

<!-- ---------------------------------------------------------------------------
	content -->
	<div class="row-fluid" id="main-content">
		<div class="span12">
