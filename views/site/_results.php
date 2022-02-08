<?php

use yii\web\View;

/* @var $this View */
/* @var $results array */

?>
<div class="container">
    <h2 class="display-4 text-center">Results</h2>
    <div class="row">
        <div class="col">
            <strong>Total ...</strong>
            <ul>
                <li><strong><?= $results['uniqueCount']['pages']; ?></strong> <span>pages crawled</span></li>
                <li><strong><?= $results['uniqueCount']['images']; ?></strong> <span>unique images</span></li>
                <li><strong><?= $results['uniqueCount']['internalLinks']; ?></strong> <span>unique internal links</span></li>
                <li><strong><?= $results['uniqueCount']['externalLinks']; ?></strong> <span>unique external links</span></li>
            </ul>
        </div>
        <div class="col">
            <strong>Average ...</strong>
            <ul>
                <li><span>page load: </span> <strong><?= $results['average']['loadSeconds']; ?> seconds</strong></li>
                <li><span>word count:</span> <strong><?= $results['average']['wordCount']; ?></strong></li>
                <li><span>title length:</span> <strong><?= $results['average']['titleLength']; ?></strong></li>
            </ul>
        </div>
    </div>
</div>
<table class="table table-striped">
    <thead>
    <tr>
        <th>page crawled</th>
        <th>HTTP status code</th>
        <th>title length</th>
        <th>word count</th>
    </tr>
    </thead>
    <tbody>
    <?php
    foreach ($results['pages'] as $url => $info) :
        ?>
        <tr>
            <td><?php
                echo $url;
                if ($url === $results['entryPoint']) :
                    echo ' <span class="badge badge-primary">entry point</span>';
                endif; ?></td>
            <td><?= $info['httpCode']; ?></td>
            <td><?= $info['titleLength']; ?></td>
            <td><?= $info['wordCount']; ?></td>
        </tr>
    <?php
    endforeach; ?>
    </tbody>
</table>
