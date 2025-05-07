        <?php
         // Get all deals of the day
$deals_of_the_day = get_deal_of_day();
        ?>
        
        <!-- Deal of the day -->
        <div class="product-featured" style="background-color: #FFE4E1;">
          <h2 class="title" style= "text-align: center; font-size: 32px;" >Deal of the day</h2>

          <div class="showcase-wrapper has-scrollbar">
            <!-- display data from db -->
            <?php
            while ($row = mysqli_fetch_assoc($deals_of_the_day)) {
            ?>

              <div class="showcase-container">
                <div class="showcase">
                  <div class="showcase-banner">
                    <img src="./admin/upload/<?php echo $row['deal_image']; ?>" alt="shampoo, conditioner & facewash packs" class="showcase-img" />
                  </div>

                  <div class="showcase-content">

                    <a href="./viewdetail.php?id=<?php echo $row['deal_id'] ?>&category=<?php echo "deal_of_day" ?>">
                      <h3 class="showcase-title">
                        <?php echo $row['deal_title']; ?>
                      </h3>
                    </a>

                    <p class="showcase-desc">
                      <?php echo $row['deal_description'] ?>
                    </p>

                    <div class="price-box">
                      <p class="price">$ <?php echo $row['deal_net_price'] ?> </p>

                      <del>$<?php echo $row['deal_discounted_price'] ?></del>
                    </div>

<a href="./viewdetail.php?id=<?php echo urlencode($row['deal_id']); ?>&category=<?php echo urlencode("deal_of_day"); ?>">
    <button class="add-cart-btn" type="button">View detail</button> 
</a>
                     
                    <div class="showcase-status">
                      <div class="wrapper">
                        <p>already sold: <b><?php echo $row['sold_deal'] ?></b></p>

                        <p>available: <b><?php echo $row['available_deal'] ?></b></p>
                      </div>
                      
                    </div>

                  </div>
                </div>
              </div>

            <?php
            }
            ?>
            <!--  -->
          </div>
        </div>