<?php require "inc/db_connect.inc.php"; ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-wEmeIV1mKuiNpC+IOBjI7aAzPcEZeedi5yW5f2yOq55WWLwNGmvvx4Um1vskeMj0" crossorigin="anonymous">
    <title>Blog Author</title>
</head>
<body class="bg-light">
<?php
require "inc/navbar.inc.php";
require "inc/sort.php";
require "inc/post_card.inc.php";
?>

<main class="container py-5">
<div class="row">
<div class="col-12">

<?php
$author_id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

if (!$author_id) {
    echo "<div class='alert alert-warning'>Author not found.</div>";
} else {
    $sql_author = "SELECT author_id, first_name, last_name FROM author WHERE author_id = :author_id";
    $stmt_author = $db->prepare($sql_author);
    $stmt_author->execute(["author_id" => $author_id]);
    $author = $stmt_author->fetch();

    if (!$author) {
        echo "<div class='alert alert-warning'>Author not found.</div>";
    } else {
        echo "<div class='d-flex justify-content-between align-items-center mb-4'>";
        echo "<h1 class='fw-bold mb-0'>Posts by " . h($author->first_name) . " " . h($author->last_name) . "</h1>";
        $sort_state = blog_sort_state();
        $sort = $sort_state['sort'];
        $page = $sort_state['page'];
        $posts_per_page = $sort_state['posts_per_page'];
        $offset = $sort_state['offset'];

        blog_sort_controls($author_id, $sort);
        echo "</div>";

        $order_by = ($sort === 'oldest') ? "ORDER BY post.date ASC" : "ORDER BY post.date DESC";

        // Get total count for pagination
        $count_sql = "SELECT COUNT(*) as total FROM post WHERE post.author = :author_id";
        
        $count_stmt = $db->prepare($count_sql);
        $count_stmt->execute(["author_id" => $author_id]);
        $count_result = $count_stmt->fetch();
        $total_posts = $count_result->total;
        $total_pages = ceil($total_posts / $posts_per_page);

        blog_sort_redirect('author.php', ['id' => $author_id, 'sort' => $sort], $page, $total_pages);

        // Get posts for current page
        $sql = "SELECT post.post_id, post.title, post.date, post.content, author.author_id, author.first_name, author.last_name
        FROM post
        JOIN author ON post.author = author.author_id
        WHERE post.author = :author_id
        " . $order_by . "
        LIMIT :limit OFFSET :offset";

        $stmt = $db->prepare($sql);
        $stmt->bindValue(':limit', $posts_per_page, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->bindValue(':author_id', $author_id, PDO::PARAM_INT);
        $stmt->execute();
        $data = $stmt->fetchAll();

        if (count($data) === 0) {
            echo "<div class='alert alert-secondary'>No posts found for this author.</div>";
        } else {
            foreach ($data as $row) {
                render_post_card($row, $db);
            }
            
            blog_sort_pagination('author.php', ['id' => $author_id, 'sort' => $sort], $page, $total_pages, $posts_per_page);
        }

        echo "<p class='mt-4'><a href='blog.php' class='text-secondary text-decoration-none'>&larr; Back to Blog</a></p>";
    }
}
?>

</div>
</div>
</main>

<?php require "inc/footer.inc.php"; ?>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0/dist/js/bootstrap.bundle.min.js" integrity="sha384-p34f1UUtsS3wqzfto5wAAmdvj+osOnFyQFpp4Ua3gs/ZVWx6oOypYoCJhGGScy+8" crossorigin="anonymous"></script>
</body>
</html>
