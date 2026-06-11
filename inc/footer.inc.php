<footer class="bg-dark text-white-50 py-4 mt-5">
  <div class="container">
    <div class="row g-4 align-items-start">
      <div class="col-12 col-md-4">
        <h2 class="h6 text-white mb-3">Site Menu</h2>
        <ul class="list-unstyled mb-0">
          <li class="mb-1"><a class="text-decoration-none text-white-50" href="blog.php">Home</a></li>
        </ul>
      </div>
      <div class="col-12 col-md-4">
        <h2 class="h6 text-white mb-3">Categories</h2>
        <ul class="list-unstyled mb-0">
          <?php
            $sql_cats = "SELECT category_id, category FROM category ORDER BY category ASC";
            $stmt_cats = $db->prepare($sql_cats);
            $stmt_cats->execute();
            $all_categories = $stmt_cats->fetchAll();

            foreach ($all_categories as $cat) {
              echo "<li class='mb-1'><a class='text-decoration-none text-white-50' href='category.php?id={$cat->category_id}'>" . h($cat->category) . "</a></li>";
            }
          ?>
        </ul>
      </div>
      <div class="col-12 col-md-4">
        <h2 class="h6 text-white mb-3">Authors</h2>
        <ul class="list-unstyled mb-0">
          <?php
            $sql_authors = "SELECT author_id, first_name, last_name FROM author ORDER BY first_name ASC, last_name ASC";
            $stmt_authors = $db->prepare($sql_authors);
            $stmt_authors->execute();
            $all_authors = $stmt_authors->fetchAll();

            foreach ($all_authors as $auth) {
              echo "<li class='mb-1'><a class='text-decoration-none text-white-50' href='author.php?id={$auth->author_id}'>" . h($auth->first_name) . " " . h($auth->last_name) . "</a></li>";
            }
          ?>
        </ul>
      </div>
    </div>
    <hr class="border-secondary my-4">
    <div class="text-center"><small>&copy; <?= date('Y') ?> CTEC 227 Blog</small></div>
  </div>
</footer>