<?php

namespace Sugarcrm\XHProf\Viewer\Templates\Run;


use Sugarcrm\XHProf\Viewer\Templates\Helpers\FormatHelper;

class QueriesTableTemplate
{
    public static function render($title, $queries, $highlightLanguage, $moreQueriesAfter = 5)
    {
        ?>
        <div class="panel panel-default panel-queries">
            <div class="panel-heading">
                <h3 class="panel-title" style="display: inline-block;">
                    <?php echo $title ?>
                    <span class="badge"><?php echo count($queries['queries']) ?></span>
                </h3>
                &nbsp;&nbsp;
                <?php static::renderTopButtons() ?>
            </div>
            <div class="panel-body">
                <?php if (count($queries['queries']) == 0) { ?>
                    There are no queries
                <?php } ?>
                <?php foreach ($queries['queries'] as $index => $query) {
                if ($index == $moreQueriesAfter) { ?>
                <div class="more-queries" style="display:none;">
                    <?php } ?>
                    <?php if ($index != 0) { ?>
                        <hr>
                    <?php } ?>
                    <div class="query">
                        <p>
                            Hits: <span class="badge"><?php echo $query['hits'] ?></span>,
                            Time: <span class="badge"><?php echo FormatHelper::microsec($query['time'] * 1E6) ?></span>,
                            <?php if (!empty($query['fetch_count'])) { ?>
                                Fetch Count: <span class="badge"><?php echo FormatHelper::number($query['fetch_count']) ?></span>,
                            <?php } ?>
                            <?php if (!empty($query['fetch_time'])) { ?>
                                Fetch Time: <span class="badge"><?php echo FormatHelper::microsec($query['fetch_time'] * 1E6)  ?></span>,
                            <?php } ?>
                            Query:
                            <span style="float:right;">
                                <?php static::renderQueryButtons(); ?>
                            </span>
                        </p>

                        <div class="query-container ugly active">
                    <pre><code class="<?php echo $highlightLanguage ?>"
                            <?php if (!empty($query['highlight_positions'])) { ?>
                                data-highlight-positions="<?php echo json_encode($query['highlight_positions']) ?>"
                            <?php } ?>><?php
                            if (is_array($query['query'])) {
                                echo htmlentities($query['query'][0]), !empty($query['query'][0]) ? "\n" : '', htmlentities(json_encode($query['query'][1], JSON_PRETTY_PRINT));
                            } else {
                                echo htmlentities($query['query']);
                            }
                            ?></code></pre>
                            <div class="show-more-button" style="display:none;"><button class="btn btn-link">Show More</button></div>
                        </div>
                        <div class="traces">
                            <button class="btn btn-link btn-show-traces">
                                <span class="glyphicon glyphicon-triangle-right" aria-hidden="true"></span>
                                <span class="badge"><?php echo count($query['traces']) ?></span>
                                unique backtrace(s) for this query
                            </button>

                            <div class="traces-list" style="display:none;">
                                <?php foreach ($query['traces'] as $traceIndex => $trace) { ?>
                                    <div class="trace">
                                        <?php if ($traceIndex != 0) { ?>
                                            <hr>
                                        <?php } ?>
                                        <p>Hits: <span class="badge"><?php echo $trace['hits'] ?></span>,
                                            Time: <span class="badge"><?php echo FormatHelper::microsec($trace['time'] * 1E6) ?></span>,
                                            Trace <button type="button" class="btn btn-default btn-xs btn-trace-with-filenames">with filenames</button>:</p>
                                        <pre class="trace-short"><code class="stylus"><?php echo $trace['content_short'] ?></code></pre>
                                        <pre class="trace-long" style="display:none;"><code class="stylus"><?php echo $trace['content'] ?></code></pre>
                                    </div>
                                <?php } ?>
                            </div>
                        </div>
                        <p></p>
                    </div>
                    <?php } ?>
                    <?php if (count($queries['queries']) > $moreQueriesAfter) { ?>
                </div>
            <?php } ?>
            </div>
            <?php if (count($queries['queries']) > $moreQueriesAfter) { ?>
                <div class="panel-footer">
                    <button class="btn btn-primary btn-show-all">Show All</button>
                </div>
            <?php } ?>
        </div>

        <?php
    }

    public static function renderQueryButtons()
    {
        ?>
            <button class="btn btn-default btn-xs btn-query-copy-to-clipboard" type="button" data-toggle="tooltip" title="Copy to clipboard">
                <i class="fa fa-clipboard" ></i>
            </button>
        <?php
    }

    public static function renderTopButtons()
    {

    }
}
