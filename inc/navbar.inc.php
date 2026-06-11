<nav class="navbar navbar-expand-lg navbar-dark bg-dark">
  <div class="container-fluid">
    <a class="navbar-brand" href="blog.php">Blog</a>
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
      <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse" id="navbarSupportedContent">
      <ul class="navbar-nav me-auto mb-2 mb-lg-0">
        <li class="nav-item">
          <a class="nav-link" href="blog.php">Home</a>
        </li>
        
        <!-- Categories Dropdown -->
        <li class="nav-item dropdown">
          <a class="nav-link dropdown-toggle" href="#" id="categoriesDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
            Categories
          </a>
          <ul class="dropdown-menu" aria-labelledby="categoriesDropdown">
            <?php 
              // Get all categories in alphabetical order
              $sql_cats = "SELECT category_id, category FROM category ORDER BY category ASC";
              $stmt_cats = $db->prepare($sql_cats);
              $stmt_cats->execute();
              $all_categories = $stmt_cats->fetchAll();
              
              foreach($all_categories as $cat) {
                echo "<li><a class='dropdown-item' href='category.php?id={$cat->category_id}'>" . h($cat->category) . "</a></li>";
              }
            ?>
          </ul>
        </li>
        
        <!-- Authors Dropdown -->
        <li class="nav-item dropdown">
          <a class="nav-link dropdown-toggle" href="#" id="authorsDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
            Authors
          </a>
          <ul class="dropdown-menu" aria-labelledby="authorsDropdown">
            <?php 
              // Get all authors
              $sql_authors = "SELECT author_id, first_name, last_name FROM author ORDER BY first_name ASC, last_name ASC";
              $stmt_authors = $db->prepare($sql_authors);
              $stmt_authors->execute();
              $all_authors = $stmt_authors->fetchAll();
              
              foreach($all_authors as $auth) {
                echo "<li><a class='dropdown-item' href='author.php?id={$auth->author_id}'>" . h($auth->first_name) . " " . h($auth->last_name) . "</a></li>";
              }
            ?>
          </ul>
        </li>
      </ul>
    </div>
  </div>
</nav>