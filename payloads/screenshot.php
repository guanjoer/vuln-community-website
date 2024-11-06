<script>
            
function XSSProtection(input) {
	return input.replace(/script|on/gi, '')
}

const searchTerms = XSSProtection('<?php echo $output; ?>');

</script>


<h2>검색어: <?php echo "<script>document.write(searchTerms)</script>"; ?></h2>


<script>
const isSafeInput = x => !/<script|<img|<input|<.*on/is.test(x);

const searchTerms = `<?php echo $output; ?>`

if(isSafeInput(searchTerms)) {
   	document.addEventListener('DOMContentLoaded', () => {
     	document.getElementById('query').innerHTML = `검색어: ${searchTerms}`;
}); 
} else {
    	 alert('Something went wrong!');
    	 history.back();
} 
</script>

<h2 id="query"></h2>