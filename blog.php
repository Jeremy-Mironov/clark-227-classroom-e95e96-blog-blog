<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <!-- Bootstrap -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-wEmeIV1mKuiNpC+IOBjI7aAzPcEZeedi5yW5f2yOq55WWLwNGmvvx4Um1vskeMj0" crossorigin="anonymous">
    <title>Blog</title>
</head>
<body class="bg-light">
<!-- Include the Bootstrap Navbar -->
<?php 
    require "inc/db_connect.inc.php"; // connect to the blog database
    require "inc/navbar.inc.php" 
?>

<main class="container py-5">
<div class="row">
<div class="col-12">

<h1 class="fw-bold mb-4">CTEC 227 Blog</h1>

<?php
// Get search term and sort option
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$sort = isset($_GET['sort']) && $_GET['sort'] === 'oldest' ? 'oldest' : 'newest';
$page = isset($_GET['page']) && is_numeric($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$posts_per_page = 5;
$offset = ($page - 1) * $posts_per_page;

// Search form
echo "<div class='card mb-4'>";
echo "<div class='card-body p-4'>";
echo "<form method='GET' class='row g-3 align-items-end'>";
echo "<div class='col-md-6'>";
echo "<label for='search' class='form-label'>Search Posts</label>";
echo "<input type='text' class='form-control' id='search' name='search' placeholder='Search by title or content...' value='" . h($search) . "'>";
echo "</div>";
echo "<div class='col-md-3'>";
echo "<label for='sort' class='form-label'>Sort By</label>";
echo "<select class='form-select' id='sort' name='sort'>";
echo "<option value='newest'" . ($sort === 'newest' ? ' selected' : '') . ">Newest First</option>";
echo "<option value='oldest'" . ($sort === 'oldest' ? ' selected' : '') . ">Oldest First</option>";
echo "</select>";
echo "</div>";
echo "<div class='col-md-3'>";
echo "<button type='submit' class='btn btn-primary w-100'>Search</button>";
if($search || $sort !== 'newest') {
    echo " <a href='blog.php' class='btn btn-outline-secondary w-100 mt-2'>Clear</a>";
}
echo "</div>";
echo "</form>";
echo "</div>";
echo "</div>";

// Build the query with search and sort
$where_clause = '';
$params = [];

if($search !== ''){
    $where_clause = "WHERE (post.title LIKE :search OR post.content LIKE :search)";
    $params['search'] = "%$search%";
}

$order_by = ($sort === 'oldest') ? "ORDER BY post.date ASC" : "ORDER BY post.date DESC";

// Get total count for pagination
$count_sql = "SELECT COUNT(*) as total FROM post " . $where_clause;
$count_stmt = $db->prepare($count_sql);
$count_stmt->execute($params);
$count_result = $count_stmt->fetch();
$total_posts = $count_result->total;
$total_pages = ceil($total_posts / $posts_per_page);

// If current page exceeds total pages, redirect to last page
if($page > $total_pages && $total_pages > 0){
    header("Location: blog.php?page=" . $total_pages . ($search ? "&search=" . urlencode($search) : '') . ($sort !== 'newest' ? "&sort=" . $sort : ''));
    exit;
}

// Get posts for current page
$sql = "SELECT post.post_id, post.title, post.date, post.content, author.author_id, author.first_name, author.last_name 
FROM post 
JOIN author ON post.author = author.author_id
" . $where_clause . "
" . $order_by . "
LIMIT :limit OFFSET :offset";

$stmt = $db->prepare($sql);
$stmt->bindValue(':limit', $posts_per_page, PDO::PARAM_INT);
$stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
foreach($params as $key => $value){
    $stmt->bindValue(':' . $key, $value);
}
$stmt->execute();
$data = $stmt->fetchAll();

// Display posts
if(count($data) === 0){
    if($search !== ''){
        echo "<div class='alert alert-info'>No posts found matching your search.</div>";
    } else {
        echo "<div class='alert alert-info'>No posts found.</div>";
    }
} else {
    foreach($data as $row){
        $date = date_create($row->date);
        
        $sql_cats = "SELECT post_category.post_id, post_category.category_id, category.category 
        FROM post_category 
        JOIN category ON post_category.category_id = category.category_id 
        WHERE post_category.post_id = :post_id";
        
        $stmt_category = $db->prepare($sql_cats);
        $stmt_category->execute(["post_id" => $row->post_id]);
        $categories = $stmt_category->fetchAll();
        
        echo "<div class='card shadow-sm mb-4'>";
        echo "<div class='card-body p-4'>";
        echo "<h2 class='h5 fw-bold mb-1'><a href='post.php?id={$row->post_id}' class='text-dark text-decoration-none'>" . h($row->title) . "</a></h2>";
        echo "<p class='text-muted small mb-2'><a href='author.php?id={$row->author_id}' class='text-decoration-none'>" . h($row->first_name) . " " . h($row->last_name) . "</a> &mdash; " . $date->format('M d, Y')  . "</p>";
        
        if(count($categories) > 0){
            echo "<div class='d-flex flex-wrap gap-1 mb-3'>";
            foreach($categories as $category_row){
                echo "<a href='category.php?id={$category_row->category_id}' class='badge rounded-pill bg-secondary text-decoration-none'>" . h($category_row->category) . "</a>";
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
        
        // Previous button
        if($page > 1){
            $prev_link = "blog.php?page=" . ($page - 1) . ($search ? "&search=" . urlencode($search) : '') . ($sort !== 'newest' ? "&sort=" . $sort : '');
            echo "<li class='page-item'><a class='page-link' href='{$prev_link}'>&larr; Previous</a></li>";
        } else {
            echo "<li class='page-item disabled'><span class='page-link'>&larr; Previous</span></li>";
        }
        
        // Page numbers
        for($i = 1; $i <= $total_pages; $i++){
            if($i === $page){
                echo "<li class='page-item active'><span class='page-link'>{$i}</span></li>";
            } else {
                $page_link = "blog.php?page={$i}" . ($search ? "&search=" . urlencode($search) : '') . ($sort !== 'newest' ? "&sort=" . $sort : '');
                echo "<li class='page-item'><a class='page-link' href='{$page_link}'>{$i}</a></li>";
            }
        }
        
        // Next button
        if($page < $total_pages){
            $next_link = "blog.php?page=" . ($page + 1) . ($search ? "&search=" . urlencode($search) : '') . ($sort !== 'newest' ? "&sort=" . $sort : '');
            echo "<li class='page-item'><a class='page-link' href='{$next_link}'>Next &rarr;</a></li>";
        } else {
            echo "<li class='page-item disabled'><span class='page-link'>Next &rarr;</span></li>";
        }
        
        echo "</ul>";
        echo "</nav>";
        
        echo "<p class='text-center text-muted mt-3'>Page {$page} of {$total_pages} (Showing {$posts_per_page} posts per page)</p>";
    }
}
?>
</div>
</div> <!-- Closing for .row -->
</main>

<footer class="bg-dark text-white-50 text-center py-4 mt-5">
    <div class="container"><small>&copy; <?= date('Y') ?> CTEC 227 Blog</small></div>
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0/dist/js/bootstrap.bundle.min.js" integrity="sha384-p34f1UUtsS3wqzfto5wAAmdvj+osOnFyQFpp4Ua3gs/ZVWx6oOypYoCJhGGScy+8" crossorigin="anonymous"></script>
</body>
</html>