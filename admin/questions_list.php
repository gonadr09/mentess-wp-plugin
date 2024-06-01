<div class="wrap">
    <?php
    echo '<h1 class="wp-heading-inline">' . get_admin_page_title() . '</h1>';
    ?>
    <a class="page-title-action">Añadir nueva</a>
    <hr class="wp-header-end">
    <h2 class="screen-reader-text">Filter plugins list</h2>
    <ul class="subsubsub">
        <li class="all"><a href="plugins.php?plugin_status=all" class="current" aria-current="page">All <span class="count">(2)</span></a> |</li>
        <li class="active"><a href="plugins.php?plugin_status=active">Active <span class="count">(2)</span></a> |</li>
        <li class="auto-update-disabled"><a href="plugins.php?plugin_status=auto-update-disabled">Auto-updates Disabled <span class="count">(2)</span></a></li>
    </ul>

    <form class="search-form search-plugins" method="get">
        <p class="search-box">
            <label class="screen-reader-text" for="plugin-search-input">Search Installed Plugins:</label>
            <input type="search" id="plugin-search-input" class="wp-filter-search" name="s" value="" placeholder="Search installed plugins..." aria-describedby="live-search-desc">
            <input type="submit" id="search-submit" class="button hide-if-js" value="Search Installed Plugins">
        </p>
    </form>

    <table class="wp-list-table widefat fixed striped table-view-list posts">
        <caption class="screen-reader-text">Table ordered by Date. Descending.</caption>
        <thead>
            <tr>
                <td id="cb" class="manage-column column-cb check-column"><input id="cb-select-all-1" type="checkbox">
                    <label for="cb-select-all-1">
                        <span class="screen-reader-text">Select All</span>
                    </label>
                </td>
                <th scope="col" id="title" class="manage-column column-title column-primary sortable desc" abbr="Title">
                    <a href="http://yardsales.local/wp-admin/edit.php?orderby=title&amp;order=asc">
                        <span>Title</span>
                        <span class="sorting-indicators">
                            <span class="sorting-indicator asc" aria-hidden="true"></span>
                            <span class="sorting-indicator desc" aria-hidden="true"></span>
                        </span>
                        <span class="screen-reader-text">Sort ascending.</span>
                    </a>
                </th>
                <th scope="col" id="author" class="manage-column column-author">Author</th>
                <th scope="col" id="categories" class="manage-column column-categories">Categories</th>
                <th scope="col" id="tags" class="manage-column column-tags">Tags</th>
                <th scope="col" id="comments" class="manage-column column-comments num sortable desc" abbr="Comments">
                    <a href="http://yardsales.local/wp-admin/edit.php?orderby=comment_count&amp;order=asc">
                        <span>
                            <span class="vers comment-grey-bubble" title="Comments" aria-hidden="true"></span>
                            <span class="screen-reader-text">Comments</span>
                        </span>
                        <span class="sorting-indicators">
                            <span class="sorting-indicator asc" aria-hidden="true"></span>
                            <span class="sorting-indicator desc" aria-hidden="true"></span>
                        </span>
                        <span class="screen-reader-text">Sort ascending.</span>
                    </a>
                </th>
                <th scope="col" id="date" class="manage-column column-date sorted desc" aria-sort="descending" abbr="Date">
                    <a href="http://yardsales.local/wp-admin/edit.php?orderby=date&amp;order=asc">
                        <span>Date</span>
                        <span class="sorting-indicators">
                            <span class="sorting-indicator asc" aria-hidden="true"></span>
                            <span class="sorting-indicator desc" aria-hidden="true"></span>
                        </span>
                    </a>
                </th>
            </tr>
        </thead>

        <tbody id="the-list">
            <tr id="post-1" class="iedit author-self level-0 post-1 type-post status-publish format-standard hentry category-uncategorized">
                <th scope="row" class="check-column"> <input id="cb-select-1" type="checkbox" name="post[]" value="1">
                    <label for="cb-select-1">
                        <span class="screen-reader-text">
                            Select Hello world! </span>
                    </label>
                    <div class="locked-indicator">
                        <span class="locked-indicator-icon" aria-hidden="true"></span>
                        <span class="screen-reader-text">
                            “Hello world!” is locked </span>
                    </div>
                </th>
                <td class="title column-title has-row-actions column-primary page-title" data-colname="Title">
                    <div class="locked-info"><span class="locked-avatar"></span> <span class="locked-text"></span></div>
                    <strong><a class="row-title" href="http://yardsales.local/wp-admin/post.php?post=1&amp;action=edit" aria-label="“Hello world!” (Edit)">Hello world!</a></strong>

                    <div class="hidden" id="inline_1">
                        <div class="post_title">Hello world!</div>
                        <div class="post_name">hello-world</div>
                        <div class="post_author">1</div>
                        <div class="comment_status">open</div>
                        <div class="ping_status">open</div>
                        <div class="_status">publish</div>
                        <div class="jj">14</div>
                        <div class="mm">09</div>
                        <div class="aa">2021</div>
                        <div class="hh">15</div>
                        <div class="mn">06</div>
                        <div class="ss">43</div>
                        <div class="post_password"></div>
                        <div class="page_template">default</div>
                        <div class="post_category" id="category_1">1</div>
                        <div class="tags_input" id="post_tag_1"></div>
                        <div class="sticky"></div>
                        <div class="post_format"></div>
                    </div>
                    <div class="row-actions"><span class="edit"><a href="http://yardsales.local/wp-admin/post.php?post=1&amp;action=edit" aria-label="Edit “Hello world!”">Edit</a> | </span><span class="inline hide-if-no-js"><button type="button" class="button-link editinline" aria-label="Quick edit “Hello world!” inline" aria-expanded="false">Quick&nbsp;Edit</button> | </span><span class="trash"><a href="http://yardsales.local/wp-admin/post.php?post=1&amp;action=trash&amp;_wpnonce=1de7556b90" class="submitdelete" aria-label="Move “Hello world!” to the Trash">Trash</a> | </span><span class="view"><a href="http://yardsales.local/hello-world/" rel="bookmark" aria-label="View “Hello world!”">View</a></span></div><button type="button" class="toggle-row"><span class="screen-reader-text">Show more details</span></button>
                </td>
                <td class="author column-author" data-colname="Author"><a href="edit.php?post_type=post&amp;author=1">ramita</a></td>
                <td class="categories column-categories" data-colname="Categories"><a href="edit.php?category_name=uncategorized">Uncategorized</a></td>
                <td class="tags column-tags" data-colname="Tags"><span aria-hidden="true">—</span><span class="screen-reader-text">No tags</span></td>
                <td class="comments column-comments" data-colname="Comments">
                    <div class="post-com-count-wrapper">
                        <a href="http://yardsales.local/wp-admin/edit-comments.php?p=1&amp;comment_status=approved" class="post-com-count post-com-count-approved"><span class="comment-count-approved" aria-hidden="true">1</span><span class="screen-reader-text">1 comment</span></a><span class="post-com-count post-com-count-pending post-com-count-no-pending"><span class="comment-count comment-count-no-pending" aria-hidden="true">0</span><span class="screen-reader-text">No pending comments</span></span>
                    </div>
                </td>
                <td class="date column-date" data-colname="Date">Published<br>2021/09/14 at 3:06 pm</td>
            </tr>
        </tbody>

        <tfoot>
            <tr>
                <td class="manage-column column-cb check-column">
                    <input id="cb-select-all-2" type="checkbox">
                    <label for="cb-select-all-2">
                        <span class="screen-reader-text">Select All</span>
                    </label>
                </td>
                <th scope="col" class="manage-column column-title column-primary sortable desc" abbr="Title">
                    <a href="http://yardsales.local/wp-admin/edit.php?orderby=title&amp;order=asc">
                        <span>Title</span>
                        <span class="sorting-indicators">
                            <span class="sorting-indicator asc" aria-hidden="true"></span>
                            <span class="sorting-indicator desc" aria-hidden="true"></span>
                        </span>
                        <span class="screen-reader-text">Sort ascending.</span>
                    </a>
                </th>
                <th scope="col" class="manage-column column-author">Author</th>
                <th scope="col" class="manage-column column-categories">Categories</th>
                <th scope="col" class="manage-column column-tags">Tags</th>
                <th scope="col" class="manage-column column-comments num sortable desc" abbr="Comments">
                    <a href="http://yardsales.local/wp-admin/edit.php?orderby=comment_count&amp;order=asc">
                        <span>
                            <span class="vers comment-grey-bubble" title="Comments" aria-hidden="true"></span>
                            <span class="screen-reader-text">Comments</span>
                        </span>
                        <span class="sorting-indicators">
                            <span class="sorting-indicator asc" aria-hidden="true"></span><span class="sorting-indicator desc" aria-hidden="true"></span></span> <span class="screen-reader-text">Sort ascending.</span></a>
                </th>
                <th scope="col" class="manage-column column-date sorted desc" aria-sort="descending" abbr="Date"><a href="http://yardsales.local/wp-admin/edit.php?orderby=date&amp;order=asc"><span>Date</span><span class="sorting-indicators"><span class="sorting-indicator asc" aria-hidden="true"></span><span class="sorting-indicator desc" aria-hidden="true"></span></span></a></th>
            </tr>
        </tfoot>

    </table>

</div>