<?php require "inc/db_connect.inc.php"; ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-wEmeIV1mKuiNpC+IOBjI7aAzPcEZeedi5yW5f2yOq55WWLwNGmvvx4Um1vskeMj0" crossorigin="anonymous">
    <title>Blog Category</title>
</head>
<body class="bg-light">
<?php
require "inc/navbar.inc.php";
$category_id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
?>

<main class="container py-5">
<div class="row">
<div class="col-12">

<?php
if (!$category_id) {
    echo "<div class='alert alert-warning'>Category not found.</div>";
} else {
    $sql_category = "SELECT category FROM category WHERE category_id = :category_id";
    $stmt_cat = $db->prepare($sql_category);
    $stmt_cat->execute(["category_id" => $category_id]);
    $category = $stmt_cat->fetch();

    if (!$category) {
        echo "<div class='alert alert-warning'>Category not found.</div>";
    } else {
        // Get sort option and pagination
        $sort = isset($_GET['sort']) && $_GET['sort'] === 'oldest' ? 'oldest' : 'newest';
        $page = isset($_GET['page']) && is_numeric($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
        $posts_per_page = 5;
        $offset = ($page - 1) * $posts_per_page;
        
        echo "<div class='d-flex justify-content-between align-items-center mb-4'>";
        echo "<h1 class='fw-bold mb-0'>Category: " . h($category->category) . "</h1>";
        
        // Sort controls
        echo "<form method='GET' class='d-flex gap-2'>";
        echo "<input type='hidden' name='id' value='{$category_id}'>";
        echo "<select class='form-select form-select-sm' name='sort' style='width: auto;' onchange='this.form.submit()'>";
        echo "<option value='newest'" . ($sort === 'newest' ? ' selected' : '') . ">Newest First</option>";
        echo "<option value='oldest'" . ($sort === 'oldest' ? ' selected' : '') . ">Oldest First</option>";
        echo "</select>";
        echo "</form>";
        echo "</div>";

        $order_by = ($sort === 'oldest') ? "ORDER BY post.date ASC" : "ORDER BY post.date DESC";

        // Get total count for pagination
        $count_sql = "SELECT COUNT(DISTINCT post.post_id) as total FROM post
        JOIN post_category ON post.post_id = post_category.post_id
        WHERE post_category.category_id = :category_id";
        
        $count_stmt = $db->prepare($count_sql);
        $count_stmt->execute(["category_id" => $category_id]);
        $count_result = $count_stmt->fetch();
        $total_posts = $count_result->total;
        $total_pages = ceil($total_posts / $posts_per_page);

        if($page > $total_pages && $total_pages > 0){
            header("Location: category.php?id={$category_id}&page=" . $total_pages . ($sort !== 'newest' ? "&sort=" . $sort : ''));
            exit;
        }

        // Get posts for current page
        $sql = "SELECT DISTINCT post.post_id, post.title, post.date, post.content, author.author_id, author.first_name, author.last_name
        FROM post
        JOIN author ON post.author = author.author_id
        JOIN post_category ON post.post_id = post_category.post_id
        WHERE post_category.category_id = :category_id
        " . $order_by . "
        LIMIT :limit OFFSET :offset";

        $stmt = $db->prepare($sql);
        $stmt->bindValue(':limit', $posts_per_page, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->bindValue(':category_id', $category_id, PDO::PARAM_INT);
        $stmt->execute();
        $data = $stmt->fetchAll();

        if (count($data) === 0) {
            echo "<div class='alert alert-secondary'>No posts were found for this category</div>";
        } else {
            foreach ($data as $row) {
                $date = date_create($row->date);

                echo "<div class='card shadow-sm mb-4'>";
                echo "<div class='card-body p-4'>";
                echo "<h2 class='h5 fw-bold mb-1'><a href='post.php?id={$row->post_id}' class='text-dark text-decoration-none'>" . h($row->title) . "</a></h2>";
                echo "<p class='text-muted small mb-2'><a href='author.php?id={$row->author_id}' class='text-decoration-none'>" . h($row->first_name) . " " . h($row->last_name) . "</a> &mdash; " . $date->format('M d, Y') . "</p>";

                $sql_post_cats = "SELECT post_category.post_id, post_category.category_id, category.category
                FROM post_category
                JOIN category ON post_category.category_id = category.category_id
                WHERE post_category.post_id = :post_id";

                $stmt_category = $db->prepare($sql_post_cats);
                $stmt_category->execute(["post_id" => $row->post_id]);
                $categories = $stmt_category->fetchAll();

                if (count($categories) > 0) {
                    echo "<div class='d-flex flex-wrap gap-1 mb-3'>";
                    foreach ($categories as $cat) {
                        echo "<a href='category.php?id={$cat->category_id}' class='badge rounded-pill bg-secondary text-decoration-none'>" . h($cat->category) . "</a>";
                    }
                    echo "</div>";
                }

                echo "<p class='text-muted mb-3'>" . h($row->content) . "</p>";
                echo "<a href='post.php?id={$row->post_id}' class='btn btn-dark btn-sm'>Read more &rsaquo;</a>";
                echo "</div>";
                echo "</div>";
            }
            
            // Pagination
            if($total_pages > 1){
                echo "<nav aria-label='Page navigation' class='mt-4'>";
                echo "<ul class='pagination justify-content-center'>";
                
                if($page > 1){
                    $prev_link = "category.php?id={$category_id}&page=" . ($page - 1) . ($sort !== 'newest' ? "&sort=" . $sort : '');
                    echo "<li class='page-item'><a class='page-link' href='{$prev_link}'>&larr; Previous</a></li>";
                } else {
                    echo "<li class='page-item disabled'><span class='page-link'>&larr; Previous</span></li>";
                }
                
                for($i = 1; $i <= $total_pages; $i++){
                    if($i === $page){
                        echo "<li class='page-item active'><span class='page-link'>{$i}</span></li>";
                    } else {
                        $page_link = "category.php?id={$category_id}&page={$i}" . ($sort !== 'newest' ? "&sort=" . $sort : '');
                        echo "<li class='page-item'><a class='page-link' href='{$page_link}'>{$i}</a></li>";
                    }
                }
                
                if($page < $total_pages){
                    $next_link = "category.php?id={$category_id}&page=" . ($page + 1) . ($sort !== 'newest' ? "&sort=" . $sort : '');
                    echo "<li class='page-item'><a class='page-link' href='{$next_link}'>Next &rarr;</a></li>";
                } else {
                    echo "<li class='page-item disabled'><span class='page-link'>Next &rarr;</span></li>";
                }
                
                echo "</ul>";
                echo "</nav>";
                
                echo "<p class='text-center text-muted mt-3'>Page {$page} of {$total_pages} (Showing {$posts_per_page} posts per page)</p>";
            }
        }

        echo "<p class='mt-2'><a href='blog.php' class='text-secondary text-decoration-none'>&larr; Back to Blog</a></p>";
    }
}
?>

</div>
</div>
</main>

<footer class="bg-dark text-white-50 text-center py-4 mt-5">
    <div class="container"><small>&copy; <?= date('Y') ?> CTEC 227 Blog</small></div>
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0/dist/js/bootstrap.bundle.min.js" integrity="sha384-p34f1UUtsS3wqzfto5wAAmdvj+osOnFyQFpp4Ua3gs/ZVWx6oOypYoCJhGGScy+8" crossorigin="anonymous"></script>
</body>
</html>
