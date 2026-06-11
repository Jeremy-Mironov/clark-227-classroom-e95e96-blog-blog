        <?php

        function blog_sort_state(int $postsPerPage = 5): array
        {
                $sort = isset($_GET['sort']) && $_GET['sort'] === 'oldest' ? 'oldest' : 'newest';
                $page = isset($_GET['page']) && is_numeric($_GET['page']) ? max(1, (int) $_GET['page']) : 1;
                $offset = ($page - 1) * $postsPerPage;

                return [
                        'sort' => $sort,
                        'page' => $page,
                        'posts_per_page' => $postsPerPage,
                        'offset' => $offset,
                ];
        }

        function blog_sort_controls(int $id, string $sort): void
        {
                echo "<form method='GET' class='d-flex gap-2'>";
                echo "<input type='hidden' name='id' value='" . h($id) . "'>";
                echo "<select class='form-select form-select-sm' name='sort' style='width: auto;' onchange='this.form.submit()'>";
                echo "<option value='newest'" . ($sort === 'newest' ? ' selected' : '') . ">Newest First</option>";
                echo "<option value='oldest'" . ($sort === 'oldest' ? ' selected' : '') . ">Oldest First</option>";
                echo "</select>";
                echo "</form>";
        }

        function blog_sort_redirect(string $pageName, array $baseParams, int $page, int $totalPages): void
        {
                if ($page > $totalPages && $totalPages > 0) {
                        $baseParams['page'] = $totalPages;
                        header('Location: ' . $pageName . '?' . http_build_query($baseParams));
                        exit;
                }
        }

        function blog_sort_pagination(string $pageName, array $baseParams, int $page, int $totalPages, int $postsPerPage): void
        {
                if ($totalPages <= 1) {
                        return;
                }

                echo "<nav aria-label='Page navigation' class='mt-4'>";
                echo "<ul class='pagination justify-content-center'>";

                if ($page > 1) {
                        $prevParams = $baseParams;
                        $prevParams['page'] = $page - 1;
                        $prev_link = $pageName . '?' . http_build_query($prevParams);
                        echo "<li class='page-item'><a class='page-link' href='" . h($prev_link) . "'>&larr; Previous</a></li>";
                } else {
                        echo "<li class='page-item disabled'><span class='page-link'>&larr; Previous</span></li>";
                }

                for ($i = 1; $i <= $totalPages; $i++) {
                        if ($i === $page) {
                                echo "<li class='page-item active'><span class='page-link'>{$i}</span></li>";
                        } else {
                                $pageParams = $baseParams;
                                $pageParams['page'] = $i;
                                $page_link = $pageName . '?' . http_build_query($pageParams);
                                echo "<li class='page-item'><a class='page-link' href='" . h($page_link) . "'>{$i}</a></li>";
                        }
                }

                if ($page < $totalPages) {
                        $nextParams = $baseParams;
                        $nextParams['page'] = $page + 1;
                        $next_link = $pageName . '?' . http_build_query($nextParams);
                        echo "<li class='page-item'><a class='page-link' href='" . h($next_link) . "'>Next &rarr;</a></li>";
                } else {
                        echo "<li class='page-item disabled'><span class='page-link'>Next &rarr;</span></li>";
                }

                echo "</ul>";
                echo "</nav>";
                echo "<p class='text-center text-muted mt-3'>Page {$page} of {$totalPages} (Showing {$postsPerPage} posts per page)</p>";
        }