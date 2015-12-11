<?php if (count($queries) == 0 ) {
    return;
} ?>
<div class="panel panel-default panel-queries">
    <div class="panel-heading">
        <h3 class="panel-title" style="display: inline-block;"><?php echo $options['title'] ?> <span class="badge"><?php echo count($queries) ?></span></h3>
        &nbsp;&nbsp;
        <?php foreach ($options['buttons'] as $button) { ?>
            <a class="btn btn-primary btn-sm" href="<?php echo $button['href'] ?>"><?php echo $button['title'] ?></a>
        <?php } ?>
    </div>
    <div class="panel-body">
        <?php foreach ($queries as $index => $query) {
            if ($index == $options['more_queries_after']) { ?>
                <div class="more-queries" style="display:none;">
            <?php } ?>
            <?php if ($index != 0) { ?>
                <hr>
            <?php } ?>
            <div class="query">
                <p>
                    Hits: <span class="badge"><?php echo $query['hits'] ?></span>,
                    Time: <span class="badge"><?php echo number_format($query['time'] * 1E6) ?> microsec</span>,
                    <?php if (!empty($query['fetch_count'])) { ?>
                        Fetch Count: <span class="badge"><?php echo number_format($query['fetch_count']) ?></span>,
                    <?php } ?>
                    <?php if (!empty($query['fetch_time'])) { ?>
                        Fetch Time: <span class="badge"><?php echo number_format($query['fetch_time'] * 1E6)  ?> microsec</span>,
                    <?php } ?>
                    Query:
                </p>
                <div class="query-container">
                    <pre><code class="<?php echo $options['hightlight_language'] ?>"><?php
                            if (is_array($query['query'])) {
                                echo htmlentities($query['query'][0]), "\n", htmlentities(json_encode($query['query'][1], JSON_PRETTY_PRINT));
                            } else {
                                echo htmlentities($query['query']);
                            }
                            ?></code></pre>
                    <div class="show-more-button" style="display:none;"><button class="btn btn-link">Show More</button></div>
                </div>
                <div class="traces">
                    <button class="btn btn-link btn-show-traces">
                        <span class="glyphicon glyphicon-triangle-right" aria-hidden="true"></span>
                        <span class="badge"><?php echo count($query['dumps']) ?></span>
                        unique backtrace(s) for this query
                    </button>

                    <div class="traces-list" style="display:none;">
                    <?php foreach ($query['dumps'] as $dumpIndex => $dump) { ?>
                        <?php if ($dumpIndex != 0) { ?>
                            <hr>
                        <?php } ?>
                        <p>Hits: <span class="badge"><?php echo $dump['hits'] ?></span>,
                            Time: <span class="badge"><?php echo number_format($dump['time'] * 1E6) ?> microsec</span>,
                            Trace:</p>
                        <pre><code><?php echo $dump['content'] ?></code></pre>
                    <?php } ?>
                    </div>
                </div>
                <p></p>
            </div>
        <?php } ?>
        <?php if (count($queries) > $options['more_queries_after']) { ?>
            </div>
        <?php } ?>
    </div>
    <?php if (count($queries) > $options['more_queries_after']) { ?>
        <div class="panel-footer">
            <button class="btn btn-primary btn-show-all">Show All</button>
        </div>
    <?php } ?>
</div>
