$(function() {
    function getVisibleInitialized($elements, initializer) {
        return function() {
            $elements.each(function(index, $element) {
                if (!$element || !$element.is(":visible")) {
                    return;
                }

                var elementTopOffset = $element.offset().top,
                    elementBottomOffset = elementTopOffset + $element.height(),
                    windowTopOffset = $(window).scrollTop(),
                    windowBottomOffset = windowTopOffset + window.innerHeight;
                if (elementBottomOffset < windowTopOffset - 100 || elementTopOffset > windowBottomOffset + 100) {
                    return;
                }

                window.setTimeout(initializer.bind(null, $element), 0);

                $elements[index] = null;
            });
        };
    }

    function initContainer($container) {
        var $queryContainer = $container.find('.query-container'),
            $showTracesButton = $container.find('.btn-show-traces'),
            $copyToClipboardButton = $container.find('.btn-query-copy-to-clipboard'),
            $tracesList = $container.find('.traces-list');

        if ($queryContainer.height() == 200) {
            var $showMoreButtonContainer = $container.find('.show-more-button'),
                $showMoreButton = $showMoreButtonContainer.find('button');

            $showMoreButtonContainer.show();
            function containerToggle(ev) {
                $queryContainer.toggleClass('query-container-expanded');
                $showMoreButton.html($queryContainer.hasClass('query-container-expanded') ? 'Show Less' : 'Show More');
                if (!$queryContainer.hasClass('query-container-expanded')) {
                    window.scrollTo(0, $container.offset().top - 10);
                }
            }

            $showMoreButton.click(containerToggle);
            $queryContainer.click(function(ev) {
                if (ev.metaKey) {
                    containerToggle(ev);
                    ev.stopPropagation();
                }
            });
        }

        $showTracesButton.click(function() {
            $tracesList.toggle();
            $showTracesButton.find('.glyphicon')
                .toggleClass('glyphicon-triangle-right')
                .toggleClass('glyphicon-triangle-bottom');

            if (!$tracesList.data('hljs-initialized')) {
                var $tracesToHighlight = $($.map($tracesList.find('.trace'), $)),
                    tracesInitializer = getVisibleInitialized($tracesToHighlight, function($trace) {
                        var $longTrace = $trace.find('pre.trace-long'),
                            $shortTrace = $trace.find('pre.trace-short'),
                            $button = $trace.find('button.btn-trace-with-filenames');

                        hljs.highlightBlock($shortTrace.find('code')[0]);
                        hljs.highlightBlock($longTrace.find('code')[0]);
                        $button.click(function() {
                            var $this = $(this);
                            $this.toggleClass('active');
                            if ($this.hasClass('active')) {
                                $longTrace.show();
                                $shortTrace.hide();
                            } else {
                                $longTrace.hide();
                                $shortTrace.show();
                            }
                        });
                    });

                tracesInitializer();
                $(window).scroll(tracesInitializer);
                $tracesList.data('hljs-initialized', true);
            }
        });

        $container.find('.query-container pre code').each(function(i, block) {
            hljs.highlightBlock(block);
            var highlightPositions = $(block).data('highlight-positions');
            if (highlightPositions && highlightPositions.length > 0) {
                highlightMatches(block, highlightPositions)
            }
        });

        $copyToClipboardButton.click(function() {
            window.getSelection().removeAllRanges();
            var $query = $container.find('.query-container pre code');
            var range = document.createRange();
            range.selectNode($query[0]);
            window.getSelection().addRange(range);
            document.execCommand('copy');
        });
    }

    var $queries = $($.map($('.query'), $)),
        initVisibleInViewport = getVisibleInitialized($queries, initContainer);

    $(window).scroll(initVisibleInViewport);
    initVisibleInViewport();

    var $panels = $('.panel-queries');
    $panels.each(function(index, panel) {
        var $panel = $(panel),
            $showAllButton = $panel.find('.panel-footer .btn-show-all'),
            $moreQueries = $panel.find('.more-queries');

        if ($showAllButton.length == 1) {

            function toggleMoreQueries() {
                $moreQueries.toggle();
                $showAllButton.html($moreQueries.is(':visible') ? 'Show Fewer' : 'Show All');
                initVisibleInViewport();
            }
            $showAllButton.click(toggleMoreQueries);

            $panel.click(function(ev) {
                if (ev.metaKey) {
                    toggleMoreQueries();
                }
            });
        }
    });

    function highlightMatches(code, matches) {
        var node = code.childNodes[0],
            fromParent = false,
            highlighting = false,
            currentPeacePosition = 0,
            seekingMatch = matches.shift(),
            seekingPosition = seekingMatch[0],
            element;

        while(node !== code) {
            if (node.nodeType == node.TEXT_NODE) {
                if (currentPeacePosition + node.textContent.length < seekingPosition) {
                    currentPeacePosition += node.textContent.length;
                    if (highlighting) {
                        element = document.createElement('span');
                        element.innerText = node.textContent;
                        element.classList.add('sql-matched-chunk');
                        node.parentNode.replaceChild(element, node);
                        node = element;
                        fromParent = true;
                    }
                } else {
                    //
                    var currentPosition = 0,
                        lastChunk = false,
                        found = false;
                    while (currentPosition < node.textContent.length) {

                        var chunkEndPosition;
                        if (seekingPosition - currentPeacePosition > node.textContent.length - 1) {
                            chunkEndPosition = node.textContent.length;
                        } else {
                            chunkEndPosition = seekingPosition - currentPeacePosition;
                            found = true;
                        }

                        if (chunkEndPosition > currentPosition) {
                            var chunk = node.textContent.substring(currentPosition, chunkEndPosition);
                            currentPosition = chunkEndPosition;

                            if (highlighting) {
                                element = document.createElement('span');
                                element.innerText = chunk;
                                element.classList.add('sql-matched-chunk');
                            } else {
                                element = document.createTextNode(chunk);
                            }
                            node.parentNode.insertBefore(element, node);
                        }

                        if (found) {
                            if (highlighting) {
                                seekingMatch = matches.shift();
                                if (seekingMatch) {
                                    seekingPosition = seekingMatch[0];
                                } else {
                                    seekingPosition = currentPeacePosition + node.textContent.length;
                                    lastChunk = true;
                                }
                            } else {
                                seekingPosition = seekingMatch[1];
                            }
                            highlighting = !highlighting;
                            found = false;
                        }
                    }

                    fromParent = true;
                    node.parentNode.removeChild(node);
                    currentPeacePosition += node.textContent.length;
                    node = element;

                    if (lastChunk) {
                        return;
                    }
                }
            }

            if (!fromParent && node.childNodes.length > 0) {
                node = node.childNodes[0];
                fromParent = false;
            } else if (node.nextSibling) {
                node = node.nextSibling;
                fromParent = false;
            } else {
                node = node.parentNode;
                fromParent = true;
            }
        }
    }
});
