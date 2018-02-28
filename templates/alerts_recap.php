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
 * along with SARU.  If not, see <http://www.gnu.org/licenses/>.
 * See LICENSE.TXT file for more information.
 *
 * Saru is released under dual license, AGPL and commercial license.
 * If you need a commercial license or if you don't know which licence you need,
 * please contact us at <info@saru.fr>
 *
 */

/**
 * TEMPLATE
 * alerts recap
 *
 * @since	1.0
 * @author	Marie Kuntz / Lézard Rouge <mariek@lezard-rouge.fr>
 */

if( ! defined('LOCAL_PATH')) { exit; }

?>

<?php if($no_account): ?>

	<div class="alert alert-info">Vous devez choisir un compte pour voir les alertes associées.</div>

<?php else: ?>

	<?php if( ! isset($alerts_list['total']) || $alerts_list['total'] == 0): ?>

		<div class="alert alert-info">Vous n'avez pas d'alerte pour l'instant. Vous pouvez en ajouter en passant
			par le menu "Alertes" &gt; "Nouvelle alerte".
		</div>

	<?php else:

		$today = date("Y-m-d");
		$next_days = date("Y-m-d", mktime(null, null, null, date("m"), date("d") + NEXT_DAYS, date("Y")));

		$box_note = array();
		$box_expired = array();
		$box_today = array();
		$box_next_days = array();
		$box_later = array();

		foreach($alerts_list['results'] as $alert):

			if (Utils::dateEmpty($alert->getDate())):
				$box_note[] = $alert;
			elseif ($alert->getDate() < $today):
				$box_expired[] = $alert;
			elseif($alert->getDate() == $today):
				$box_today[] = $alert;
			elseif($alert->getDate() < $next_days):
				$box_next_days[] = $alert;
			else:
				$box_later[] = $alert;
			endif;

		endforeach;
?>

<div class="row-fluid">
	<div class="span10">
		<div class="row-fluid">

	<!-- ---------------------------------------------------------------------------
		expired alerts -->
	<?php if( ! empty($box_expired)): ?>
	<fieldset class="span6 box-alert-expired">
		<legend>En retard</legend>
		<?php foreach ($box_expired as $alert): ?>
		<div class="alert-home"><?php if($alert->getPriority() == 1): ?>
			<img src="images/alert_p1.png" alt="priority" class="icon">
		<?php endif;
			echo $alert->getDate_format(); ?> :
			<?php echo $alert->getContact()->getFullname(); ?>
			<a href="contacts.php?tpl=recap&contact_id=<?php echo $alert->getContact_id(); ?>"><img src="images/detail.png" alt="Accéder à la fiche" title="Accéder à la fiche" class="icon"></a>
			<a href="alerts.php?tpl=form&alert_id=<?php echo $alert->getId(); ?>"><img src="images/edit.png" alt="Modifier" title="Modifier" class="icon"></a>
			<a href="alerts.php?tpl=done&alert_id=<?php echo $alert->getId(); ?>&<?php echo TOKEN_PREFIX; ?>token=<?php echo $session->getSessionData('token'); ?>"><img src="images/checked.png" alt="Fait" title="Fait" class="icon"></a>
			<br>
			<?php echo Utils::n2br($alert->getComments()); ?>
		<?php if(count($users) > 1): ?>
			<br><span class="assigned-to">Assigné à <?php echo $alert->getUser()->getFirstname() . ' ' . $alert->getUser()->getLastname(); ?></span>
		<?php endif; ?>
		</div>
		<?php endforeach; ?>
	</fieldset>
	<?php endif; ?>

	<!-- ---------------------------------------------------------------------------
		today alerts -->
	<?php if( ! empty($box_today)): ?>
	<fieldset class="span6 box-alert-today">
		<legend>Aujourd'hui</legend>
		<?php foreach ($box_today as $alert): ?>
		<div class="alert-home"><?php if($alert->getPriority() == 1): ?>
			<img src="images/alert_p1.png" alt="priority" class="icon">
		<?php endif;
			echo $alert->getDate_format(); ?> : <?php echo $alert->getContact()->getFullname(); ?>
			<a href="contacts.php?tpl=recap&contact_id=<?php echo $alert->getContact_id(); ?>"><img src="images/detail.png" alt="Accéder à la fiche" title="Accéder à la fiche" class="icon"></a>
			<a href="alerts.php?tpl=form&alert_id=<?php echo $alert->getId(); ?>"><img src="images/edit.png" alt="Modifier" title="Modifier" class="icon"></a>
			<a href="alerts.php?tpl=done&alert_id=<?php echo $alert->getId(); ?>&<?php echo TOKEN_PREFIX; ?>token=<?php echo $session->getSessionData('token'); ?>"><img src="images/checked.png" alt="Fait" title="Fait" class="icon"></a>
			<br>
			<?php echo Utils::n2br($alert->getComments()); ?>
		<?php if(count($users) > 1): ?>
			<br><span class="assigned-to">Assigné à <?php echo $alert->getUser()->getFirstname() . ' ' . $alert->getUser()->getLastname(); ?></span>
		<?php endif; ?>
		</div>
		<?php endforeach; ?>
	</fieldset>
	<?php endif; ?>

		</div><!-- .row-fluid -->

		<div class="row-fluid">

	<!-- ---------------------------------------------------------------------------
		next days alerts -->
	<?php if( ! empty($box_next_days)): ?>
	<fieldset class="span6 box-alert-next-days">
		<legend>Dans les <?php echo NEXT_DAYS; ?> prochains jours</legend>
		<?php foreach ($box_next_days as $alert): ?>
		<div class="alert-home"><?php if($alert->getPriority() == 1): ?>
			<img src="images/alert_p1.png" alt="priority" class="icon">
		<?php endif;
			echo $alert->getDate_format(); ?> : <?php echo $alert->getContact()->getFullname(); ?>
			<a href="contacts.php?tpl=recap&contact_id=<?php echo $alert->getContact_id(); ?>"><img src="images/detail.png" alt="Accéder à la fiche" title="Accéder à la fiche" class="icon"></a>
			<a href="alerts.php?tpl=form&alert_id=<?php echo $alert->getId(); ?>"><img src="images/edit.png" alt="Modifier" title="Modifier" class="icon"></a>
			<a href="alerts.php?tpl=done&alert_id=<?php echo $alert->getId(); ?>&<?php echo TOKEN_PREFIX; ?>token=<?php echo $session->getSessionData('token'); ?>"><img src="images/checked.png" alt="Fait" title="Fait" class="icon"></a>
			<br>
			<?php echo Utils::n2br($alert->getComments()); ?>
		<?php if(count($users) > 1): ?>
			<br><span class="assigned-to">Assigné à <?php echo $alert->getUser()->getFirstname() . ' ' . $alert->getUser()->getLastname(); ?></span>
		<?php endif; ?>
		</div>
		<?php endforeach; ?>
	</fieldset>
	<?php endif; ?>

	<!-- ---------------------------------------------------------------------------
		later alerts -->
	<?php if( ! empty($box_later)): ?>
	<fieldset class="span6 box-alert-later">
		<legend>Plus tard</legend>
	<?php foreach ($box_later as $alert): ?>
		<div class="alert-home"><?php if($alert->getPriority() == 1): ?>
			<img src="images/alert_p1.png" alt="priority" class="icon">
		<?php endif;
			echo $alert->getDate_format(); ?> : <?php echo $alert->getContact()->getFullname(); ?>
			<a href="contacts.php?tpl=recap&contact_id=<?php echo $alert->getContact_id(); ?>"><img src="images/detail.png" alt="Accéder à la fiche" title="Accéder à la fiche" class="icon"></a>
			<a href="alerts.php?tpl=form&alert_id=<?php echo $alert->getId(); ?>"><img src="images/edit.png" alt="Modifier" title="Modifier" class="icon"></a>
			<a href="alerts.php?tpl=done&alert_id=<?php echo $alert->getId(); ?>&<?php echo TOKEN_PREFIX; ?>token=<?php echo $session->getSessionData('token'); ?>"><img src="images/checked.png" alt="Fait" title="Fait" class="icon"></a>
			<br>
			<?php echo Utils::n2br($alert->getComments()); ?>
		<?php if(count($users) > 1): ?>
			<br><span class="assigned-to">Assigné à <?php echo $alert->getUser()->getFirstname() . ' ' . $alert->getUser()->getLastname(); ?></span>
		<?php endif; ?>
		</div>
	<?php endforeach; ?>
	</fieldset>
	<?php endif; ?>

	<?php endif; ?>

<?php endif; ?>

		</div><!-- .row-fluid -->
	</div><!-- .span8 -->

	<div class="span2">

<!-- ---------------------------------------------------------------------------
	notes -->
	<?php if( ! empty($box_note)): ?>
	<div class="span12">
		<?php foreach ($box_note as $alert): ?>
		<div class="box-alert-note">
			<div class="box-alert-note-legend">
			<?php if($alert->getPriority() == 1): ?>
				<img src="images/alert_p1.png" alt="priority" class="icon">
			<?php endif; ?>
				<a href="contacts.php?tpl=recap&contact_id=<?php echo $alert->getContact_id(); ?>"><img src="images/detail.png" alt="Accéder à la fiche" title="Accéder à la fiche" class="icon"></a>
				<a href="alerts.php?tpl=form&alert_id=<?php echo $alert->getId(); ?>"><img src="images/edit.png" alt="Modifier" title="Modifier" class="icon"></a>
				<a href="alerts.php?tpl=done&alert_id=<?php echo $alert->getId(); ?>&<?php echo TOKEN_PREFIX; ?>token=<?php echo $session->getSessionData('token'); ?>"><img src="images/checked.png" alt="Fait" title="Fait" class="icon"></a>
			</div>
			<div class="box-alert-note-content">
				<?php echo $alert->getContact()->getFullname(); ?>
				<br>
				<?php echo Utils::n2br($alert->getComments()); ?>
			<?php if(count($users) > 1): ?>
				<br><span class="assigned-to">Assigné à <?php echo $alert->getUser()->getFirstname() . ' ' . $alert->getUser()->getLastname(); ?></span>
			<?php endif; ?>
			</div>
		</div>
		<?php endforeach; ?>
	</div>
	<?php endif; ?>

	</div><!-- .span2 -->
</div><!-- .row-fluid -->
