<?php if ($shortSymbolName) { ?>
    <span data-toggle="tooltip" title="<?php echo htmlentities($symbolName) ?>">
        <?php echo htmlentities($shortSymbolName) ?>
    </span>
<?php } else { ?>
    <?php echo htmlentities($symbolName) ?>
<?php } ?>
