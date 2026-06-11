<?php

function render_post_card(object $post, PDO $db): void
{
    $date = date_create($post->date);

    $sql_categories = "SELECT post_category.post_id, post_category.category_id, category.category
    FROM post_category
    JOIN category ON post_category.category_id = category.category_id
    WHERE post_category.post_id = :post_id";

    $stmt_categories = $db->prepare($sql_categories);
    $stmt_categories->execute(['post_id' => $post->post_id]);
    $categories = $stmt_categories->fetchAll();

    $sql_tags = "SELECT post_tag.post_id, post_tag.tag_id, tag.tag
    FROM post_tag
    JOIN tag ON post_tag.tag_id = tag.id
    WHERE post_tag.post_id = :post_id";

    $stmt_tags = $db->prepare($sql_tags);
    $stmt_tags->execute(['post_id' => $post->post_id]);
    $tags = $stmt_tags->fetchAll();

    echo "<div class='card shadow-sm mb-4'>";
    echo "<div class='card-body p-4'>";
    echo "<h2 class='h5 fw-bold mb-1'><a href='post.php?id={$post->post_id}' class='text-dark text-decoration-none'>" . h($post->title) . "</a></h2>";
    echo "<p class='text-muted small mb-2'><a href='author.php?id={$post->author_id}' class='text-decoration-none'>" . h($post->first_name) . " " . h($post->last_name) . "</a> &mdash; " . $date->format('M d, Y') . "</p>";

    if (count($categories) > 0) {
        echo "<div class='d-flex flex-wrap gap-2 mb-2'>";
        echo "<span class='text-muted'>Categories:</span>";
        foreach ($categories as $category) {
            echo "<a href='category.php?id={$category->category_id}' class='text-decoration-none text-primary'> " . h($category->category) . "</a>";
        }
        echo "</div>";
    }

    if (count($tags) > 0) {
        echo "<div class='d-flex flex-wrap gap-1 mb-3'>";
        foreach ($tags as $tag) {
            echo "<span class='badge rounded-pill bg-dark text-decoration-none'>" . h($tag->tag) . "</span>";
        }
        echo "</div>";
    }

    echo "<p class='text-muted mb-3'>" . h($post->content) . "</p>";
    echo "<a href='post.php?id={$post->post_id}' class='btn btn-dark btn-sm'>Read more &rsaquo;</a>";
    echo "</div>";
    echo "</div>";
}