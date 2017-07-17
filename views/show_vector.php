<!doctype html>
<html lang="en">
	<head>
		<meta charset="utf-8">
		<meta name="viewport" content="width=device-width, initial-scale=1">
		<meta name="author" content="CJ Patoilo">
		<meta name="description" content="Milligram provides a minimal setup of styles for a fast and clean starting point. Specially designed for better performance and higher productivity with fewer properties to reset resulting in cleaner code.">
		<title>Show Vector | Information Retrieval Project</title>
		<link rel="icon" href="//milligram.io/images/icon.png">
		<link rel="stylesheet" href="//fonts.googleapis.com/css?family=Roboto:300,300italic,700,700italic">
		<link rel="stylesheet" href="//cdn.jsdelivr.net/normalize/3.0.3/normalize.min.css">
		<link rel="stylesheet" href="/irp/assets/css/milligram.min.css">
		<link rel="stylesheet" href="//milligram.io/styles/main.css">
		<link rel="stylesheet" href="/irp/assets/css/style.css">
	</head>
	<body>

		<main class="wrapper">

			<nav class="navigation">
				<section class="container">
					<a class="navigation-title" href="/irp/">
						<svg class="img" version="1.1" viewBox="0 0 463 669">
							<g transform="translate(0.000000,669.000000) scale(0.100000,-0.100000)">
								<path d="M2303 6677c-11-13-58-89-393-627-128-206-247-397-265-425-18-27-85-135-150-240-65-104-281-451-480-770-358-575-604-970-641-1032-10-18-45-74-76-126-47-78-106-194-107-212-1-3-11-26-24-53-60-118-132-406-157-623-19-158-8-491 20-649 82-462 291-872 619-1213 192-199 387-340 646-467 335-165 638-235 1020-235 382 0 685 70 1020 235 259 127 454 268 646 467 328 341 537 751 619 1213 28 158 39 491 20 649-25 217-97 505-157 623-13 27-23 50-23 53 0 16-57 127-107 210-32 52-67 110-77 128-37 62-283 457-641 1032-199 319-415 666-480 770-65 105-132 213-150 240-18 28-137 219-265 425-354 570-393 630-400 635-4 3-12-1-17-8zm138-904c118-191 654-1050 1214-1948 148-236 271-440 273-452 2-13 8-23 11-23 14 0 72-99 125-212 92-195 146-384 171-598 116-974-526-1884-1488-2110-868-205-1779 234-2173 1046-253 522-257 1124-10 1659 45 97 108 210 126 225 4 3 9 13 13 22 3 9 126 209 273 445 734 1176 1102 1766 1213 1946 67 108 124 197 126 197 2 0 59-89 126-197zM1080 3228c-75-17-114-67-190-243-91-212-128-368-137-580-34-772 497-1451 1254-1605 77-15 112-18 143-11 155 35 212 213 106 329-32 36-62 48-181 75-223 50-392 140-552 291-115 109-178 192-242 316-101 197-136 355-128 580 3 111 10 167 30 241 30 113 80 237 107 267 11 12 20 26 20 32 0 6 8 22 17 36 26 41 27 99 3 147-54 105-142 149-250 125z"></path>
							</g>
						</svg>
						&nbsp;
						<h1 class="title">Information Retrieval Project</h1>
					</a>
					<ul class="navigation-list float-right">
						<li class="navigation-item">
							<a class="navigation-link" href="/irp/">Home</a>
						</li>
						<li class="navigation-item">
							<a class="navigation-link" href="#popover-grid1" data-popover>Indexing</a>
							<div class="popover" id="popover-grid1">
								<ul class="popover-list">
									<li class="popover-item"><a class="popover-link" href="/irp/index/create" title="Create Index">Create Index</a></li>
									<li class="popover-item"><a class="popover-link" href="/irp/index/show" title="Show Index">Show Index</a></li>
									<li class="popover-item"><a class="popover-link" href="/irp/corpus" title="Show Corpus">Show Corpus</a></li>
								</ul>
							</div>
						</li>
						<li class="navigation-item">
							<a class="navigation-link" href="/irp/weighting">Weighting</a>
						</li>
						<li class="navigation-item">
							<a class="navigation-link active" href="#popover-grid2" data-popover>Vector</a>
							<div class="popover" id="popover-grid2">
								<ul class="popover-list">
									<li class="popover-item"><a class="popover-link" href="/irp/vector/count" title="Count Vector">Count Vector</a></li>
									<li class="popover-item"><a class="popover-link" href="/irp/vector/show" title="Show Vector">Show Vector</a></li>
								</ul>
							</div>
						</li>
						<li class="navigation-item">
							<a class="navigation-link" href="/irp/retrieval">Retrieval</a>
						</li>
						<li class="navigation-item">
							<a class="navigation-link" href="/irp/cache">Cache</a>
						</li>
					</ul>
				</section>
			</nav>
		 
			<section class="container">
				<center>
					<h3>Hasil Penghitungan Vector</h3>
				</center>
				<div id="app">
					<table>
					<thead>
						<tr>
							<th>No</th>
							<th>Doc ID</th>
							<th>Vector Length</th>
						</tr>
					</thead>
					<tbody>
						<tr v-for="(item, index) in list">
							<td v-html="index+1"></td>
							<td v-html="item.doc_id"></td>
							<td v-html="item.length"></td>
						</tr>
					</tbody>
					</table>
					<infinite-loading :on-infinite="onInfinite" ref="infiniteLoading"></infinite-loading>
				</div>
            </section>

			<footer class="footer">
				<section class="container">
					<p>Created by <a href="#">The Dev</a>.</p>
				</section>
			</footer>

		</main>

		<!-- import javascript -->
		<script src="//cdn.jsdelivr.net/npm/vue@2.4.1/dist/vue.min.js"></script>
		<script src="//cdn.jsdelivr.net/npm/vue-resource@1.3.4/dist/vue-resource.min.js"></script>
		<script src="//cdn.jsdelivr.net/npm/vue-infinite-loading@2.1.3/dist/vue-infinite-loading.js"></script>
		<script src="/irp/assets/js/main.js"></script>
		<script>
		var api = '/irp/ajax/vector';

		new Vue({
		el: '#app',
		data: {
			list: []
		},
		methods: {
			onInfinite: function () {
				this.$http.get(api, {
					params: {
						page: Math.ceil(this.list.length / 10) + 1,
					},
				}).then((res) => {
					if (res.data.length > 0) {
						this.list = this.list.concat(res.data);
						this.$refs.infiniteLoading.$emit('$InfiniteLoading:loaded');
					} else {
						this.$refs.infiniteLoading.$emit('$InfiniteLoading:complete');
					}
				});
			}
		}
		});
		</script>
	</body>
</html>
