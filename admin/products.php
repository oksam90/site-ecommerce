<?php 
require_once $_SERVER['DOCUMENT_ROOT'].'/Ecom/core/init.php';
include 'includes/head.php';
include 'includes/navigation.php';
if (isset($_GET['add']) || isset($_GET['edit'])) {
$brandQuery = $db->query("SELECT * FROM brand ORDER BY brand");
$parentQuery = $db->query("SELECT * FROM categories WHERE parent = 0 ORDER BY category");
	$title = ((isset($_POST['title']) && $_POST['title'] != '')?sanitize($_POST['title']):'');
	$brand = ((isset($_POST['brand']) && !empty($_POST['brand']))?sanitize($_POST['brand']):'');
	if (isset($_GET['edit'])) {
		$edit_id = (int)$_GET['edit'];
		$productResults = $db->query("SELECT * FROM products WHERE id = '$edit_id'");
		$product = mysqli_fetch_assoc($productResults);
		$title = ((isset($_POST['title']) && $_POST['title'] != '')?sanitize($_POST['title']):$product['title']);
		$brand = ((isset($_POST['brand']) && $_POST['brand'] != '')?sanitize($_POST['brand']):$product['brand']);
	}
if($_POST){
	$categories = sanitize($_POST['child']);
	$price = sanitize($_POST['price']);
	$list_price = sanitize($_POST['list_price']);
	$sizes = sanitize($_POST['sizes']);
	$description = sanitize($_POST['description']);
	$dbpath = '';
	$errors= array();
	if(!empty($_POST['sizes'])){
		$sizeString = sanitize($_POST['sizes']);
		$sizeString = rtrim($sizeString,',');
		$sizesArray = explode(',',$sizeString);
		$sArray = array();
		$qArray = array();
		foreach ($sizesArray as $ss) {
		$s = explode(':', $ss);
		$sArray[] = $s[0];
		$qArray[] = $s[1];
	}
	}else{$sizesArray = array();}
	$required = array('title', 'brand', 'price', 'parent', 'child', 'sizes');
	foreach ($required as $field) {
		if ($_POST[$field] == '') {
			$errors[] = 'All fields with and asterisk are required.';
			break;
		}
	}
	if(!empty($_FILES)){
		var_dump($_FILES);
		$photo = $_FILES['photo'];
		$name = $photo['name'];
		$nameArray = explode('.',$name);
		$fileName = $nameArray[0];
		$fileExt = $nameArray[1];
		$mime = explode('/',$photo['type']);
		$mimeType = $mime[0];
		$mimeExt = $mime[1];
		$tmpLoc = $photo['tmp_name'];
		$fileSize = $photo['size'];
		$allowed = array('png','jpg','jpeg','gif');
		$uploadName = md5(microtime()).'.'.$fileExt;
		$uploadPath = BASEURL.'images/products/'.$uploadName;
		$dbpath = '/Ecom/images/products/'.$uploadName;
		if ($mimeType != 'image') {
			$errors[] = 'The file must be an image.';
		}
		if (!in_array($fileExt, $allowed)) {
			$errors[] = 'The file extension must be a png, jpg, jpeg, or gif.';
		}
		if ($fileSize > 15000000) {
			$errors[] = 'The file size must be a  under 15MB.';
		}
		if ($fileExt != $mimeExt && ($mimeExt == 'jpeg' && $fileExt != 'jpg')) {
			$errors[] = 'File extension does not match the file.';
		}
	}
	if (!empty($errors)) {
		echo display_errors($errors);	
	}else{
		//upload file and insert into database
		move_uploaded_file($tmpLoc, $uploadPath);
		$insertSql = "INSERT INTO products (`title`,`price`,`list_price`,`brand`,`categories`,`sizes`,`image`,`description`) VALUES ('$title','$price','$list_price','$brand','$categories','$sizes','$dbpath','$description')";
		$db->query($insertSql);
		header('Location: products.php');
	}
}
 ?>
 <h2 class="text-center"><?=((isset($_GET['edit']))?'Edit':'Add A New');?>Product</h2><hr>
 <form action="products.php?<?=((isset($_GET['edit']))?'edit ='.$edit_id:'add=1');?>" method="POST" enctype="multipart/form-data">
 	<div class="form-group col-md-3">
 		<label for="title">Title*:</label>
 		<input type="text" name="title" class="form-control" id="title" value="<?=$title;?>">
 	</div>
 	<div class="form-group col-md-3">
 		<label for="brand">Brand*:</label>
 		<select class="form-control" id="brand" name="brand">
 			<option value=""<?=(($brand == '')?' selected':'');?>></option>
 			<?php while($b = mysqli_fetch_assoc($brandQuery)): ?>
 				<option value="<?=$brand['id'];?>"<?=(($brand == $b['id'])?' selected':'');?>><?=$b['brand'];?></option>
 			<?php endwhile; ?>
 		</select>
 	</div>
 	<div class="form-group col-md-3">
 		<label for="parent">Parent Category*:</label>
 		<select class="form-control" id="parent" name="parent">
 			<option value=""<?=((isset($_POST['parent']) && $_POST['parent'] == '')?' selected':'');?>></option>
 				<?php while($parent = mysqli_fetch_assoc($parentQuery)): ?>
 				<option value="<?=$parent['id'];?>"<?=((isset($_POST['parent']) && $_POST['parent'] == $parent['id'])?' select':'');?>><?=$parent['category'];?></option>
 			<?php endwhile; ?>
 		</select>
 	</div> 
 	<div class="form-group col-md-3">
 		<label for="child">Child Category*:</label>
 		<select id="child" name="child" class="form-control">
 			
 		</select>
 	</div>
 	<div class="form-group col-md-3">
 		<label for="price">Price*:</label>
 		<input type="text" name="price" class="form-control" value="<?=((isset($_POST['price']))?sanitize($_POST['price']):'');?>">
 	</div>
 	<div class="form-group col-md-3">
 		<label for="price">List Price:</label>
 		<input type="text" name="list_price" class="form-control" value="<?=((isset($_POST['list_price']))?sanitize($_POST['list_price']):'');?>">
 	</div>
 	<div class="form-group col-md-3">
 		<label >Quantity & Sizes*:</label>
 		<button class="btn btn-info form-control" onclick="jQuery('#sizesModal').modal('toggle');return false;">Quantity & Sizes</button>
 	</div>
 	<div class="form-group col-md-3">
 		<label for="sizes">Sizes & Qty Preview</label>
 		<input type="text" name="sizes" class="form-control" id="sizes" value="<?=((isset($_POST['sizes']))?$_POST['sizes']:'');?>" readonly>
 	</div> 
 	<div class="form-group col-md-6">
 		<label for="photo">Product Photo:</label>
 		<input type="file" name="photo" id="photo" class="form-control">
 	</div> 
 	<div class="form-group col-md-6">
 		<label for="description">Description:</label>
 		<textarea id="description" name="description" class="form-control" rows="6"><?=((isset($_POST['description']))?sanitize($_POST['description']):'');?></textarea>
 	</div>
 	<div class="form-group pull-right">
 		<a href="products.php" class="btn btn-info">Cancel</a>
 	<input type="submit" value="<?=((isset($_GET['edit']))?'Edit':'Add');?> Product" class="btn btn-success">
 </div><div class="clearfix"></div>
 </form>
 <!-- Modal -->
<div class="modal fade" id="sizesModal" tabindex="-1" role="dialog" aria-labelledby="sizesModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
        <h4 class="modal-title" id="sizesModalLabel">Sizes & Quantity</h4>
      </div>
      <div class="modal-body">
	      	<div class="container-fluid">
		       	<?php for ($i=1; $i <= 12; $i++): ?>
		       		<div class="form-group col-md-4">
		       			<label for="size<?=$i;?>">Size:</label>
		       			<input type="text" name="size<?=$i;?>" id="size<?=$i;?>" value="<?=((!empty($sArray[$i-1]))?$sArray[$i-1]:'');?>" class="form-control">
		       		</div>
		       		<div class="form-group col-md-2">
		       			<label for="qty<?=$i;?>">Quantity:</label>
		       			<input type="number" name="qty<?=$i;?>" id="qty<?=$i;?>" value="<?=((!empty($qArray[$i-1]))?$qArray[$i-1]:'');?>" min="0" class="form-control">
		       		</div>
		       	<?php endfor; ?>
	      </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
        <button type="button" class="btn btn-primary" onclick="updateSizes();jQuery('#sizesModal').modal('toggle');return false;">Save changes</button>
      </div>
    </div>
  </div>
</div>	
<?php }else{
$sql = "SELECT * FROM products WHERE deleted = 0";
$presults = $db->query($sql);
if (isset($_GET['featured'])) {
	$id = (int)$_GET['id'];
	$featured = (int)$_GET['featured'];
	$featuredSql = "UPDATE products SET featured = '$featured' WHERE id = '$id'";
	$db->query($featuredSql);
	header('Location: products.php');
}
 ?>
<h2 class="text-center">Products</h2>
<a href="products.php?add=1" class="btn btn-success pull-right" id="add-product-btn">Add Product</a><div class="clearfix"></div>
<hr>
<table class="table table-bordered table-condensed table-striped">
	<thead><th></th><th>Products</th><th>Price</th><th>Category</th><th>Featured</th><th>Sold</th></thead>
	<tbody>

		<?php while($product = mysqli_fetch_assoc($presults)): 
			$childID = $product['categories'];
			$catSql = "SELECT * FROM categories WHERE id = '$childID'";
			$result = $db->query($catSql);
			$child = mysqli_fetch_assoc($result);
			$parentID = $child['parent'];
			$pSql = "SELECT * FROM categories WHERE id = '$parentID'";
			$presult =  $db->query($pSql);
			$parent = mysqli_fetch_assoc($presult);
			$category = $parent['category'].'-'.$child['category'];
			?>
			<tr>
				<td>
					<a href="products.php?edit=<?=$product['id'];?>" class="btn btn-xs btn-info"><span class="glyphicon glyphicon-pencil"></span></a>
					<a href="products.php?delete=<?=$product['id'];?>" class="btn btn-xs btn-danger"><span class="glyphicon glyphicon-remove"></span></a>
				</td>
				<td><?=$product['title']; ?></td>
				<td><?=money($product['price']); ?></td>
				<td><?=$category;?></td>
				<td><a href="products.php?featured=<?=(($product['featured'] == 0)?'1':'0');?>&id=<?=$product['id'];?>" class="btn btn-xs btn-primary">
					<span class="glyphicon glyphicon-<?=(($product['featured'] == 1)?'minus':'plus');?>"></span>
				</a>&nbsp <?=(($product['featured'] == 1)?'Featured Product':'');?>
</td>
				<td>0</td>
			</tr>
		<?php endwhile; ?> 
	</tbody>
</table>

  <?php } include 'includes/footer.php'; ?>