<?php
/**
 * nav.php
 *
 * This file generates the main navigation through the documentation
 * sections. It does not handle the site navigation or anything other than
 * the list of documentation sections and children within a single collection.
 */
?>

<?php foreach ($links as $folder => $files) : ?>

	<?php if (! is_numeric($folder)) :?>
		<h3><?= $folder ?></h3>

		<?php if (count($files)) : ?>
			<ul class="nav nav-stacked">
			<?php foreach ($files as $file) : ?>
				<li>
					<a href="<?= $file ?>"><?= $file ?></a>
				</li>
			<?php endforeach; ?>
			</ul>
		<?php endif; ?>
	<?php endif; ?>

<?php endforeach; ?>
