<!-- views/partials/footer.php -->
<footer class="footer mt-auto py-3">
    <div class="container text-center">
        <span class="footer-text">©  Habit Tracker. CSE370, Spring 2025</span>
    </div>
</footer>

    <!-- Bootstrap Bundle with Popper -->
    <script src="https://code.jquery.com/jquery-3.7.1.min.js" integrity="sha256-/JqT3SQfawRcv/BIHPThkBvs0OEvtFFmqPF/lYI/Cxo=" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js" integrity="sha384-C6RzsynM9kWDrMNeT87bh95OGNyZPhcTNXj1NW7RuBCsyN/o0jlpcV8Qyq46cDfL" crossorigin="anonymous"></script>
    
    <!-- Main JavaScript -->
    <script src="<?php echo strpos($_SERVER['PHP_SELF'], '/views/') !== false ? '../' : ''; ?>assets/js/main.js"></script>
    
    <!-- Theme Switching JavaScript -->
    <script src="<?php echo strpos($_SERVER['PHP_SELF'], '/views/') !== false ? '../' : ''; ?>assets/js/theme.js"></script>
</body>
</html>