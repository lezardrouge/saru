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
 * company recap : company info, contacts, meetings, alerts
 *
 * @since	1.0
 * @author	Marie Kuntz / Lézard Rouge <mariek@lezard-rouge.fr>
 */

$infos = array();
if(isset($company_infos[$company->getId()])):
	$infos = $company_infos[$company->getId()];
endif;
?>
<h1><img src="images/company.png" alt="[entreprise]" class="icon"><?php echo $company->getName(); ?> (<?php echo $company->getType()->getName(); ?>)</h1>

<?php echo $message; ?>

<div class="row-fluid">

<!-- ###########################################################################
	COMPANY INFOS -->

<div class="span4">

	<table class="trecap">
		<tr>
			<th colspan="2"><img src="images/company.png" alt="[company]" class="icon">Entreprise</th>
		</tr>
<?php
$even_odd = true;
if(MOD_CRM == 1):
	$bgcolor = ($even_odd ? "even":"odd");
?>
		<tr class="<?php echo $bgcolor; ?>">
			<td>Date de création&nbsp;:</td>
			<td><?php echo Utils::date2Fr($company->getDate_add()); ?></td>
		</tr>
<?php
		$even_odd = !$even_odd;
		$bgcolor = ($even_odd ? "even":"odd");
?>
		<tr class="<?php echo $bgcolor; ?>">
			<td>Dernière mise à jour&nbsp;:</td>
			<td><?php echo Utils::date2Fr($company->getDate_update()); ?></td>
		</tr>
<?php
	$even_odd = !$even_odd;
endif;
$bgcolor = ($even_odd ? "even":"odd");
?>
		<tr class="<?php echo $bgcolor; ?>">
			<td>Type d'entreprise&nbsp;:</td>
			<td><?php echo $company->getType()->getName(); ?></td>
		</tr>
<?php
		$even_odd = ! $even_odd;
		$bgcolor = ($even_odd ? "even":"odd");
?>
		<tr class="<?php echo $bgcolor; ?>">
			<td>Entreprise&nbsp;:</td>
			<td><?php echo $company->getName(); ?></td>
		</tr>

		<!-- -----------------------------------------------------------------------
			METAS -->
<?php $even_odd = true;
foreach ($cie_metas['results'] as $meta):
		$bgcolor = ($even_odd ? "even":"odd");
?>
			<tr class="<?php echo $bgcolor; ?>">
				<td><?php echo $meta->getName(); ?>&nbsp;:</td>
				<td><?php if(isset($infos[$meta->getId()])): echo $infos[$meta->getId()]; endif; ?></td>
			</tr>
<?php $even_odd = ! $even_odd;
endforeach;
$bgcolor = ($even_odd ? "even":"odd");
?>
		<tr class="<?php echo $bgcolor; ?>">
			<td colspan="2" class="comments"><?php echo Utils::n2br($company->getComments()); ?></td>
		</tr>
<?php if(Access::userCanAccess('company_form', false)): ?>
		<tr class="<?php echo $bgcolor; ?>">
			<td colspan="2" class="action"><a href="?tpl=form&company_id=<?php echo $company->getId(); ?>"><img src="images/edit.png" alt="Modifier" class="icon" >Modifier</a></td>
		</tr>
<?php endif; ?>
	</table>


	<!-- ###########################################################################
		COMPANY ATTACHMENTS -->
<?php if(isset($company_attachments[$company->getId()]) && ! empty($company_attachments[$company->getId()])): ?>

	<table class="trecap">
		<tr>
			<th colspan="3"><img src="images/attach.png" alt="[fichiers joints]" class="icon">Fichiers joints</th>
		</tr>
	<?php foreach($company_attachments[$company->getId()] as $attachment): ?>
		<tr>
			<td><a href="attachments.php?tpl=download&attachment_id=<?php echo $attachment->getId(); ?>"><img src="images/file_download.png" alt="download"></a></td>
			<td><a href="attachments.php?tpl=del&attachment_id=<?php echo $attachment->getId(). "&" . TOKEN_PREFIX . "token=" . $session->getSessionData('token'); ?>"><img src='images/del.png' alt='Supprimer'></a></td>
			<td><a href='attachments.php?tpl=download&attachment_id=<?php echo $attachment->getId(); ?>'><?php echo $attachment->getReal_name(); ?></a></td>
		</tr>
	<?php endforeach; ?>
	</table>
<?php endif; ?>


<?php if(Access::userCanAccess('contact_list', false)): ?>
<!-- ---------------------------------------------------------------------------
	CONTACTS -->

	<table class="trecap">
		<tr>
			<th colspan="4"><img src="images/contact.png" alt="[contacts]" class="icon">Contacts
				<a href="contacts.php?tpl=form_s&contact_id=0&company_id=<?php echo $company->getId(); ?>" rel="fancybox" class="iframe edit-portrait"><img src="images/add.png" alt="+" class="icon"></a></th>
		</tr>

	<?php if($contacts_list['total'] == 0): ?>
		<tr><td colspan="4">Aucun contact.</td></tr>
	<?php else:

		$even_odd = true;

		foreach($contacts_list['results'] as $contact):
			$bgcolor = ($even_odd ? "even":"odd");
?>
		<tr class="<?php echo $bgcolor; ?>">
			<td class="action">
			<?php if(Access::userCanAccess('contact_recap', false)): ?>
				<a href="contacts.php?tpl=recap&contact_id=<?php echo $contact->getId(); ?>"><img src="images/recap.png" alt="Fiche" /></a>
			<?php endif; ?>
			</td>
			<td><?php if(Access::userCanAccess('contact_recap', false)): ?>
				<a href="contacts.php?tpl=recap&contact_id=<?php echo $contact->getId(); ?>" class="invisible-link"><?php echo $contact->getLastname(); ?> <?php echo $contact->getFirstname(); ?></a>
			<?php else: ?>
				<?php echo $contact->getLastname(); ?> <?php echo $contact->getFirstname(); ?>
			<?php endif; ?>
			<br>
			<?php if(isset($contact_infos[$contact->getId()][1])): echo $contact_infos[$contact->getId()][1] . "<br>"; endif; ?>
			<?php if(isset($contact_infos[$contact->getId()][4])): ?>
			<a href='mailto:<?php echo $contact_infos[$contact->getId()][4]; ?>'><?php echo $contact_infos[$contact->getId()][4]; ?></a><br>
			<?php endif; ?>
			<?php if(isset($contact_infos[$contact->getId()][2])): echo $contact_infos[$contact->getId()][2]; ?><br><?php endif; ?>
			<?php if(isset($contact_infos[$contact->getId()][3])): echo $contact_infos[$contact->getId()][3]; endif; ?>
			</td>
			<td class="action">
			<?php if(Access::userCanAccess('meeting_form', false)): ?>
				<a href="meetings.php?tpl=form_s&contact_id=<?php echo $contact->getId(); ?>&meeting_id=0" rel="fancybox" class="iframe edit-portrait">
					<img src="images/calendar.png" alt="+" class="icon">
				</a><br>
			<?php endif; ?>
			<?php if(Access::userCanAccess('alert_form', false)): ?>
				<a href="alerts.php?tpl=form_s&contact_id=<?php echo $contact->getId(); ?>&alert_id=0" rel="fancybox" class="iframe edit-portrait">
					<img src="images/alerte.png" alt="+" class="icon">
				</a>
			<?php endif; ?>
			</td>
			<td class="action">
			<?php if(Access::userCanAccess('contact_form', false)): ?>
				<a href="contacts.php?tpl=form_s&contact_id=<?php echo $contact->getId(); ?>" rel="fancybox" class="iframe edit-portrait">
					<img src="images/edit.png" alt="Edit" class="icon">
				</a><br>
			<?php endif; ?>
			<?php if(Access::userCanAccess('contact_del', false)): ?>
				<a href="contacts.php?tpl=del&contact_id=<?php echo $contact->getId(); ?>" rel="fancybox" class="iframe fancydel del">
					<img src="images/del.png" alt="Del" class="icon">
				</a>
			<?php endif; ?>
		</tr>
<?php
			$even_odd = ! $even_odd;
		endforeach; ?>
	<?php endif; ?>
	</table>
<?php endif; ?>

</div> <!-- .span4 -->


<!-- ###########################################################################
	INFORMATION FLOW -->

<div class="span8">

<?php if(Access::userCanAccess('alert_list', false)): ?>
<!-- ---------------------------------------------------------------------------
	ALERTS -->
	<?php if(isset($alerts_list['total']) && $alerts_list['total'] > 0): ?>
	<table class="trecap">
		<tr>
			<th><img src="images/alerte.png" alt="[alertes]" class="icon">Alertes</th>
		</tr>
		<tr>
			<td>
		<?php foreach($alerts_list['results'] as $alert):
			if(Utils::dateEmpty($alert->getDate())):
				$class = "alert alert-note";
			elseif($alert->getDate() < date("Y-m-d")):
				$class = "alert-expired";
			else:
				$class = "alert alert-in-date";
			endif;
		?>
				<div class="<?php echo $class; ?>"><?php if($alert->getPriority() == 1): ?>
					<img src="images/alert_p1.png" alt="priority" class="icon">
				<?php endif; ?><?php
				if( ! Utils::dateEmpty($alert->getDate())):
					echo "<b>" . $alert->getDate_format() . "</b> ";
				endif;
				echo $alert->getContact()->getFirstname(); ?> <?php echo $alert->getContact()->getLastname(); ?><br>
					<?php echo Utils::n2br($alert->getComments()); ?><br>
					<a href="alerts.php?tpl=form_s&alert_id=<?php echo $alert->getId(); ?>" rel="fancybox" class="iframe edit-portrait no_underline"><img src="images/edit.png" alt="Modifier" title="Modifier" class="icon"></a>
					<a href="alerts.php?tpl=done&alert_id=<?php echo $alert->getId(); ?>&<?php echo TOKEN_PREFIX; ?>token=<?php echo $session->getSessionData('token'); ?>"><img src="images/checked.png" alt="Fait" title="Fait" class="icon"></a>
				</div>
		<?php endforeach; ?></td>
		</tr>
	</table>
	<?php endif; ?>

<?php endif; ?>


<?php if(Access::userCanAccess('meeting_list', false)): ?>
	<!-- ---------------------------------------------------------------------------
		MEETINGS -->
	<div class="break-before"></div>

	<table class="tlist2">
		<tr>
			<th colspan="5"><img src="images/calendar.png" alt="[<?php echo Utils::mb_ucfirst($lang->meetings); ?>]" class="icon"><?php echo Utils::mb_ucfirst($lang->meetings); ?></th>
		</tr>
<?php if($meetings_list['total'] == 0): ?>
		<tr><td><?php echo Utils::mb_ucfirst($lang->no_meeting); ?>.</td></tr>
<?php else:

		$even_odd = true;

		foreach($meetings_list['results'] as $meeting):
			$bgcolor = ($even_odd ? "even":"odd");
?>
		<tr class="<?php echo $bgcolor; ?>">
			<td class="date"><?php echo $meeting->getDate_format(); ?></td>
			<td><?php echo $meetings_contacts[$meeting->getContact_id()]->getName(); ?></td>
			<td class="comments"><img src="images/meeting_type_<?php echo $meeting->getType_id(); ?>.png" alt="<?php echo $meeting->getType_name(); ?>" title="<?php echo $meeting->getType_name(); ?>" class="icon">
				<?php echo Utils::n2br($meeting->getComments()); ?>
			<?php if(isset($meeting_attachments[$meeting->getId()])): ?>
				<br><br>
				<?php foreach($meeting_attachments[$meeting->getId()] as $attachment):
					$links = "<a href='attachments.php?tpl=download&attachment_id=" . $attachment->getId() . "'><img src='images/file_download.png' alt='download'></a> "
						. "<a href='attachments.php?tpl=del&attachment_id=" . $attachment->getId() . "&" . TOKEN_PREFIX . "token=" . $session->getSessionData('token') . "'><img src='images/del.png' alt='Supprimer'></a>";
				?>
				<span class="popover-attachment" data-content="<?php echo $links; ?>"><img src="images/attach.png" alt="<?php echo $attachment->getReal_name(); ?>"><?php echo $attachment->getReal_name(); ?></span>
				<?php endforeach; ?>
			<?php endif; ?>
			</td>

			<td class="action">
			<?php if(Access::userCanAccess('meeting_form', false)): ?>
				<a href="meetings.php?tpl=form_s&meeting_id=<?php echo $meeting->getId(); ?>" rel="fancybox" class="iframe edit-portrait no_underline"><img src="images/edit.png" alt="Edit" ></a>
			<?php endif; ?>
			</td>
			<td class="action">
			<?php if(Access::userCanAccess('meeting_del', false)): ?>
				<a href="meetings.php?tpl=del&meeting_id=<?php echo $meeting->getId(); ?>&p=<?php echo $p; ?>" rel="fancybox" class="iframe del"><img src="images/del.png" alt="Del" ></a>
			<?php endif; ?>
			</td>
		</tr>
		<?php $even_odd = ! $even_odd;
		endforeach; ?>
	<?php endif; ?>
	</table>
<?php endif; ?>
</div><!-- .span8 -->

</div><!-- .row-fluid -->

<script>
	$('.popover-attachment').popover({ placement: 'top', html: true });
</script>
