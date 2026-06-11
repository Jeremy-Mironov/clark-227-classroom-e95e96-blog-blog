<?php require "inc/db_connect.inc.php"; ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-wEmeIV1mKuiNpC+IOBjI7aAzPcEZeedi5yW5f2yOq55WWLwNGmvvx4Um1vskeMj0" crossorigin="anonymous">
    <title>Blog Post</title>
</head>
<body class="bg-light">
<?php require "inc/navbar.inc.php"; ?>

<main class="container py-5">
<div class="row justify-content-center">

<?php
$post_id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

if (!$post_id) {
    echo "<div class='col-12'><div class='alert alert-warning'>Post not found.</div></div>";
} else {
    $sql = "SELECT post.post_id, post.title, post.date, post.content, author.author_id, author.first_name, author.last_name
    FROM post
    JOIN author ON post.author = author.author_id
    WHERE post.post_id = :post_id";

    $stmt = $db->prepare($sql);
    $stmt->execute(["post_id" => $post_id]);
    $post = $stmt->fetch();

    if ($post) {
        $date = date_create($post->date);

        $sql_categories = "SELECT post_category.post_id, post_category.category_id, category.category
        FROM post_category
        JOIN category ON post_category.category_id = category.category_id
        WHERE post_category.post_id = :post_id";

        $stmt_category = $db->prepare($sql_categories);
        $stmt_category->execute(["post_id" => $post->post_id]);
        $categories = $stmt_category->fetchAll();

        $sql_tags = "SELECT post_tag.post_id, post_tag.tag_id, tag.tag
        FROM post_tag
        JOIN tag ON post_tag.tag_id = tag.id
        WHERE post_tag.post_id = :post_id";

        $stmt_tags = $db->prepare($sql_tags);
        $stmt_tags->execute(["post_id" => $post->post_id]);
        $tags = $stmt_tags->fetchAll();

        echo "<div class='col-12 col-lg-8 offset-lg-2'>";
        echo "<div class='card shadow-sm'>";
        echo "<div class='card-body p-4 p-md-5'>";
        echo "<h1 class='fw-bold'>" . h($post->title) . "</h1>";
        echo "<hr>";
        echo "<p class='text-muted small'><a href='author.php?id={$post->author_id}' class='text-decoration-none'>" . h($post->first_name) . " " . h($post->last_name) . "</a> &mdash; " . $date->format('M d, Y') . "</p>";

        if (count($categories) > 0) {
            echo "<div class='d-flex flex-wrap gap-2 mb-2'>";
            echo "<span class='text-muted'>Categories:</span>";
            foreach ($categories as $cat) {
                echo "<a href='category.php?id={$cat->category_id}' class='text-decoration-none text-primary'>Category - " . h($cat->category) . "</a>";
            }
            echo "</div>";
        }

        if (count($tags) > 0) {
            echo "<div class='d-flex flex-wrap gap-2 mb-3'>";
            echo "<span class='text-muted'>Tags:</span>";
            foreach ($tags as $tag) {
                echo "<span class='badge rounded-pill bg-dark'>" . h($tag->tag) . "</span>";
            }
            echo "</div>";
        }

        echo "<hr>";
        echo "<p class='mt-3 lh-lg'>" . nl2br(h($post->content)) . "</p>";
        echo "</div>";
        echo "</div>";
        echo "<p class='mt-3'><a href='blog.php' class='text-secondary text-decoration-none'>&larr; Back to Blog</a></p>";
        echo "</div>";
    } else {
        echo "<div class='col-12'><div class='alert alert-warning'>Post not found.</div></div>";
    }
}
?>

</div>
</main>

<?php require "inc/footer.inc.php"; ?>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0/dist/js/bootstrap.bundle.min.js" integrity="sha384-p34f1UUtsS3wqzfto5wAAmdvj+osOnFyQFpp4Ua3gs/ZVWx6oOypYoCJhGGScy+8" crossorigin="anonymous"></script>
</body>
</html>
