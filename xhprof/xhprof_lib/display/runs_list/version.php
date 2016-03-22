<?php if ($version = $this->versionHelper->getCurrentVersion()) { ?>
    <?php if ($version['type'] == 'hash') {?>
        <?php echo substr($version['version'], 0, 7) ?>
    <?php } else { ?>
        v<?php echo $version['version'] ?>
    <?php } ?>
<?php } ?>
