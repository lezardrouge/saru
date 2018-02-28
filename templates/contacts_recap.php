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
 * contact recap : all contact information and meetings
 *
 * @since	1.0
 * @author	Marie Kuntz / Lézard Rouge <mariek@lezard-rouge.fr>
 */
$infos = array();
if(isset($contact_infos[$contact->getId()])):
	$infos = $contact_infos[$contact->getId()];
endif;

$company_id = $company->getId();
?>
<h1><img src="images/contact.png" alt="[contact]" class="icon"><?php echo $contact->getFullname(); ?> (<?php echo $contact->getType()->getName(); ?>)</h1>

<div class="row-fluid">
	<div class="span2">
		<a href="meetings.php?tpl=form_s&contact_id=<?php echo $contact->getId(); ?>&meeting_id=0" rel="fancybox" class="iframe edit-portrait no_underline">
			<img src="images/calendar.png" alt="+" class="icon"><?php echo Utils::mb_ucfirst($lang->new_meeting); ?>
		</a>
	</div>
	<div class="span2">
		<a href="alerts.php?tpl=form_s&contact_id=<?php echo $contact->getId(); ?>&alert_id=0" rel="fancybox" class="iframe edit-portrait no_underline">
			<img src="images/alerte.png" alt="+" class="icon">Nouvelle alerte
		</a>
	</div>
</div>

<?php echo $message; ?>

<div class="row-fluid">

<!-- ###########################################################################
	CONTACT INFOS -->
<div class="span4">
	<table class="trecap">
		<tr>
			<th colspan="2"><img src="images/contact.png" alt="[contact]" class="icon">Fiche personnelle</th>
		</tr>
<?php
$even_odd = true;
if(MOD_CRM == 1):
	$bgcolor = ($even_odd ? "even":"odd");
?>
		<tr class="<?php echo $bgcolor; ?>">
			<td>Date de création&nbsp;:</td>
			<td><?php echo Utils::date2Fr($contact->getDate_add()); ?></td>
		</tr>
<?php
	$even_odd = ! $even_odd;
	$bgcolor = ($even_odd ? "even":"odd");
?>
		<tr class="<?php echo $bgcolor; ?>">
			<td>Dernière mise à jour&nbsp;:</td>
			<td><?php echo Utils::date2Fr($contact->getDate_update()); ?></td>
		</tr>
<?php
	$even_odd = ! $even_odd;
endif;
if(empty($company_id)):
	$bgcolor = ($even_odd ? "even":"odd");
?>
		<tr class="<?php echo $bgcolor; ?>">
			<td>Type de contact&nbsp;:</td>
			<td><?php echo $contact->getType()->getName(); ?></td>
		</tr>
<?php $even_odd = ! $even_odd;
endif; ?>

<?php foreach ($metas['results'] as $meta):
	$bgcolor = ($even_odd ? "even":"odd"); ?>
		<tr class="<?php echo $bgcolor; ?>">
			<td><?php echo $meta->getName(); ?>&nbsp;:</td>
			<td><?php if(isset($infos[$meta->getId()])): echo $infos[$meta->getId()]; endif; ?></td>
		</tr>
<?php $even_odd = ! $even_odd;
endforeach;

$comments = $contact->getComments();
if( ! empty($comments)):
	$bgcolor = ($even_odd ? "even":"odd");
?>
		<tr class="<?php echo $bgcolor; ?>">
			<td colspan="2" class="comments"><?php echo Utils::n2br($contact->getComments()); ?></td>
		</tr>
<?php
	$even_odd = ! $even_odd;
endif;

if(Access::userCanAccess('contact_form', false)):
	$bgcolor = ($even_odd ? "even":"odd");
?>
		<tr class="<?php echo $bgcolor; ?>">
			<td colspan="2" class="action">
				<a href="?tpl=form&contact_id=<?php echo $contact->getId(); ?>"><img src="images/edit.png" alt="Modifier" class="icon">Modifier</a>
			</td>
		</tr>
<?php endif; ?>
	</table>

	<!-- ###########################################################################
		CONTACT ATTACHMENTS -->
<?php if(isset($contact_attachments[$contact->getId()]) && ! empty($contact_attachments[$contact->getId()])): ?>

	<table class="trecap">
		<tr>
			<th colspan="3"><img src="images/attach.png" alt="[fichiers joints]" class="icon">Fichiers joints</th>
		</tr>
	<?php foreach($contact_attachments[$contact->getId()] as $attachment): ?>
		<tr>
			<td><a href="attachments.php?tpl=download&attachment_id=<?php echo $attachment->getId(); ?>"><img src="images/file_download.png" alt="download"></a></td>
			<td><a href="attachments.php?tpl=del&attachment_id=<?php echo $attachment->getId(). "&" . TOKEN_PREFIX . "token=" . $session->getSessionData('token'); ?>"><img src='images/del.png' alt='Supprimer'></a></td>
			<td><a href='attachments.php?tpl=download&attachment_id=<?php echo $attachment->getId(); ?>'><?php echo $attachment->getReal_name(); ?></a></td>
		</tr>
	<?php endforeach; ?>
	</table>
<?php endif; ?>

<?php
if(Access::userCanAccess('company_recap', false)):

	if( ! empty($company_id)):

		$infos_cie = array();
		if(isset($company_infos[$company->getId()])):
			$infos_cie = $company_infos[$company->getId()];
		endif;
?>
	<!-- ###########################################################################
		COMPANY INFOS -->

	<table class="trecap">
		<tr>
			<th colspan="2"><img src="images/company.png" alt="[company]" class="icon">Entreprise</th>
		</tr>
<?php
		$even_odd = true;
		if(MOD_CRM == 1):
?>
		<tr class="<?php echo ($even_odd ? "even":"odd"); ?>">
			<td>Date de création&nbsp;:</td>
			<td><?php echo Utils::date2Fr($contact->getCompany()->getDate_add()); ?></td>
		</tr>
		<?php $even_odd = ! $even_odd; ?>
		<tr class="<?php echo ($even_odd ? "even":"odd"); ?>">
			<td>Dernière mise à jour&nbsp;:</td>
			<td><?php echo Utils::date2Fr($contact->getCompany()->getDate_update()); ?></td>
		</tr>
<?php
			$even_odd = ! $even_odd;
		endif;
?>
		<tr class="<?php echo ($even_odd ? "even":"odd"); ?>">
			<td>Type d'entreprise&nbsp;:</td>
			<td><?php echo $contact->getType()->getName(); ?></td>
		</tr>
		<?php $even_odd = ! $even_odd; ?>
		<tr class="<?php echo ($even_odd ? "even":"odd"); ?>">
			<td>Entreprise&nbsp;:</td>
			<td><?php echo $contact->getCompany()->getName(); ?></td>
		</tr>

		<!-- -----------------------------------------------------------------------
			METAS -->
<?php
		foreach ($cie_metas['results'] as $meta):
			$even_odd = ! $even_odd;
			$bgcolor = ($even_odd ? "even":"odd");
?>
			<tr class="<?php echo $bgcolor; ?>">
				<td><?php echo $meta->getName(); ?>&nbsp;:</td>
				<td><?php if(isset($infos_cie[$meta->getId()])): echo $infos_cie[$meta->getId()]; endif; ?></td>
			</tr>
<?php
		endforeach;
		$even_odd = ! $even_odd;
		$bgcolor = ($even_odd ? "even":"odd");
		$comments = $company->getComments();
		if( ! empty($comments)):
?>
		<tr class="<?php echo $bgcolor; ?>">
			<td colspan="2" class="comments"><?php echo Utils::n2br($company->getComments()); ?></td>
		</tr>
		<?php $even_odd = ! $even_odd;
		endif;
		$bgcolor = ($even_odd ? "even":"odd"); ?>
		<tr class="<?php echo $bgcolor; ?>">
			<td class="">
				<a href="companies.php?tpl=form&company_id=<?php echo $company->getId(); ?>"><img src="images/edit.png" alt="Edit" class="icon">Modifier</a>
			</td>
			<td class="" style="text-align: right;">
				<a href="companies.php?tpl=recap&company_id=<?php echo $company->getId(); ?>"><img src="images/recap.png" alt="Recap" class="icon">Voir la fiche</a>
			</td>
		</tr>
	</table>
	<?php endif; ?>
<?php endif; ?>

</div> <!-- .span4 -->



<!-- ###########################################################################
	INFORMATION FLOW -->

<div class="span8">

<?php if(Access::userCanAccess('alert_list', false)): ?>
<!-- ---------------------------------------------------------------------------
	ALERTS -->
	<?php if($alerts_list['total'] > 0): ?>
	<table class="trecap">
		<tr>
			<th><img src="images/alerte.png" alt="[alerts]" class="icon">Alertes</th>
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
					echo "<b>" . $alert->getDate_format() . "</b><br>";
				endif;
				echo Utils::n2br($alert->getComments()); ?><br>
					<a href="alerts.php?tpl=form_s&alert_id=<?php echo $alert->getId(); ?>" rel="fancybox" class="iframe edit-portrait no_underline"><img src="images/edit.png" alt="Modifier" title="Modifier" class="icon"></a>
					<a href="alerts.php?tpl=done&alert_id=<?php echo $alert->getId(); ?>&<?php echo TOKEN_PREFIX; ?>token=<?php echo $session->getSessionData('token'); ?>"><img src="images/checked.png" alt="Fait" title="Fait" class="icon"></a>
				</div>
		<?php endforeach; ?>
			</td>
		</tr>
	</table>
	<?php endif; ?>
<?php endif; ?>


<?php if(Access::userCanAccess('meeting_list', false)): ?>
<!-- ---------------------------------------------------------------------------
	MEETINGS -->

	<table class="tlist2">
		<tr>
			<th colspan="4"><img src="images/calendar.png" alt="[<?php echo Utils::mb_ucfirst($lang->meetings); ?>]" class="icon"><?php echo Utils::mb_ucfirst($lang->meetings); ?></th>
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
				<a href="meetings.php?tpl=del&meeting_id=<?php echo $meeting->getId(); ?>" rel="fancybox" class="iframe del"><img src="images/del.png" alt="Del" ></a>
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
