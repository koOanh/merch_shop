<?php
    // --- PHP Includes (Unchanged) ---
    include_once('./includes/headerNav.php'); // Assumes this includes <!DOCTYPE>, <html>, <head> with CSS/JS links, and opens <body>
    include_once('./includes/restriction.php');
    // --- End PHP Includes ---

    /*
    NOTE: The CSS styles previously in the <head> tag below should be moved
    to your main CSS file linked within the actual <head> section
    (likely managed by headerNav.php). For example:

    body, html { height: 100%; } // Optional: If using vh units for centering wrapper
    body { display: flex; flex-direction: column; } // Allows header/footer + growing main content
    .main-content-wrapper { // Add this class to your main content area after header, before footer
        flex-grow: 1;
        display: flex;
        align-items: center; // Vertical centering
        justify-content: center; // Horizontal centering
        padding-top: 2rem; // Example padding
        padding-bottom: 2rem; // Example padding
    }
    */
?>

<div class="container main-content-wrapper" style="display: flex; justify-content: center; align-items: center;">
    <div class="card shadow-sm" style="max-width: 800px; width: 100%;"> <div class="card-body p-4 p-md-5"> <h1 class="card-title text-center mb-4 h2">Add New Product</h1> <form action="save-post.php" method="POST" enctype="multipart/form-data" class="row g-3 needs-validation" novalidate>

                <div class="col-12">
                    <label for="prodTitle" class="form-label">Title</label>
                    <input type="text" class="form-control" id="prodTitle" name="prod-title" placeholder="Enter product name" required>
                    <div class="invalid-feedback">Please provide a product title.</div>
                </div>

                <div class="col-md-6">
                    <label for="prodPrice" class="form-label">Price</label>
                    <div class="input-group"> <span class="input-group-text">$</span>
                       <input type="number" class="form-control" id="prodPrice" name="prod-price" step="0.01" min="0" placeholder="e.g., 19.99" required>
                       <div class="invalid-feedback">Please enter a valid price.</div>
                    </div>
                </div>

                <div class="col-md-6">
                    <label for="prodDiscount" class="form-label">Discount</label>
                     <div class="input-group"> <input type="number" class="form-control" id="prodDiscount" name="prod-discount" min="0" max="100" value="0" required>
                        <span class="input-group-text">%</span>
                         <div class="invalid-feedback">Enter discount (0-100).</div>
                    </div>
                </div>

                <div class="col-12">
                    <label for="prodDesc" class="form-label">Description</label>
                    <textarea class="form-control" id="prodDesc" name="prod-desc" rows="4" placeholder="Enter detailed product description" required></textarea>
                    <div class="invalid-feedback">Please enter a description.</div>
                </div>

                <div class="col-md-6">
                    <label for="prodStock" class="form-label">No. of Items (Stock)</label>
                    <input type="number" class="form-control" id="prodStock" name="noofitem" min="0" placeholder="e.g., 50" required>
                    <div class="invalid-feedback">Please enter the available quantity.</div>
                </div>

                <div class="col-md-6">
                    <label for="prodCategory" class="form-label">Category</label>
                    <select class="form-select" id="prodCategory" name="prod-category" required>
                        <option value="" disabled selected>Choose...</option> <option value="all">All</option>
                        <option value="men">Men</option>
                        <option value="women">Women</option>
                        </select>
                    <div class="invalid-feedback">Please select a category.</div>
                </div>

                <div class="col-12">
                    <label for="prodImg" class="form-label">Image</label>
                    <input type="file" class="form-control" id="prodImg" name="prod-img" accept="image/*" required>
                    <div class="invalid-feedback">Please upload a product image.</div>
                </div>


                <div class="col-12 text-center mt-4">
                    <button type="submit" name="submit" class="btn btn-lg" style="background-color:pink;">Add Product</button>
<a href="post.php" class="btn btn-secondary btn-lg ms-2">Cancel</a> 
                </div>
	

            </form>
            </div> </div> </div> <?php
    // Assuming you might have a footer include file for closing body/html tags
    // include_once('./includes/footer.php');
?>

<script>
    // Example starter JavaScript for disabling form submissions if there are invalid fields
    (function () {
      'use strict';
      window.addEventListener('load', function () {
        // Fetch all the forms we want to apply custom Bootstrap validation styles to
        var forms = document.getElementsByClassName('needs-validation');
        // Loop over them and prevent submission
        var validation = Array.prototype.filter.call(forms, function (form) {
          form.addEventListener('submit', function (event) {
            if (form.checkValidity() === false) {
              event.preventDefault();
              event.stopPropagation();
            }
            form.classList.add('was-validated');
          }, false);
        });
      }, false);
    })();
</script>