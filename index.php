<?php
require 'vendor/autoload.php';
require 'function.php';

// Disable timeout because we do long process
set_time_limit(0);

use EasyRequest\Client as HttpClient;

// Register mysqli db connector
Flight::register('db', 'mysqli', ['localhost', 'root', '', 'irp'], function($db) {
    $db->set_charset('utf8');
});

// Register Stoplist method
Flight::map('stoplist', function($words) {
    $wordlist = loadData('stopwords.txt');

    foreach ($words as $i => $word) {
        if (isset($wordlist[$word])) {
            unset($words[$i]);
        }
    }

    return $words;
});

// Register Stemming method
Flight::map('stemming', function($words) {
    foreach ($words as $word) {
        /* 1. Cek Kata di Kamus */
        if (!checkDict($word)) {
            /* 2. Buang Infection suffixes (\-lah", \-kah", \-ku", \-mu", atau \-nya") */
            $word = removeInflectionSuffix($word);

            /* 3. Buang Derivation suffix (\-i" or \-an") */
            $word = removeDerivationSuffix($word);

            /* 4. Buang Derivation prefix */
            $word = removeDerivationPrefix($word);
        }

        $result[] = $word;
    }

    return $result;
});

// Register Fetch Data method for fetching data from remote
Flight::map('fetchData', function($method, $params) {
    // Send request
    $response = HttpClient::get("http://www.hirupmotekar.com/api/${method}/", [
        'query' => $params
    ]);

    // Decode json
    $data = json_decode($response->getBody());

    return $data;
});

// Register count similarities method for data retrieval
Flight::map('countSimilarities', function($query) {
    $db = Flight::db();

    $resn = $db->query('SELECT COUNT(*) as n FROM vector');
    $rown = $resn->fetch_assoc();
    $n = $rown['n'];

    $aQuery = explode(' ', $query);

    $queryLength = 0;
    $aQueryWeight = [];

    foreach ($aQuery as $q) {
        $resNTerm = $db->query("SELECT COUNT(*) as N FROM indexes WHERE term = '$q'");
        $rowNTerm = $resNTerm->fetch_assoc();
        $nTerm = $rowNTerm['N'];

        $idf = $nTerm > 0 ? log($n / $nTerm) : 0;

        $aQueryWeight[] = $idf;

        $queryLength = $queryLength + $idf * $idf;
    }

    $queryLength = sqrt($queryLength);

    $similar = 0;

    $res = $db->query('SELECT * FROM vector ORDER BY doc_id');
    while ($row = $res->fetch_assoc()) {
        $dotProduct = 0;
        
        $id = $row['doc_id'];
        $length = $row['length'];

        $resTerm = $db->query("SELECT * FROM indexes WHERE doc_id = $id");
        while ($rowTerm = $resTerm->fetch_assoc()) {
            for ($i=0; $i < count($aQuery); $i++) { 
                if ($rowTerm['term'] == $aQuery[$i]) {
                    $dotProduct = $dotProduct + $rowTerm['weight'] * $aQueryWeight[$i];
                }
            }
        }

        if ($dotProduct > 0) {
            $sim = $dotProduct / ($queryLength * $length);

            $db->query("INSERT INTO cache (doc_id, query, value) VALUES ($id, '$query', $sim)");
            $similar++;
        }
    }

    if ($similar == 0) {
        $db->query("INSERT INTO cache (doc_id, query, value) VALUES (0, '$query', 0)");
    }
});

// Route create index
Flight::route('/index/create', function() {
    $start = microtime(true);

    $db = Flight::db();

    $tdata = [
        'new' => 0,
        'update' => 0
    ];

    $res = $db->query('SELECT date FROM docs ORDER BY id DESC LIMIT 1');
    $row = $res->fetch_assoc();

    $data = Flight::fetchData('get_posts', [
        'count' => -1,
        'date_query' => [
            [
                'after' => $row['date']
            ],
            'column' => 'post_modified'
        ],
        'order' => 'ASC'
    ]);

    if ($data->count > 0) {
        foreach ($data->posts as $doc) {
            $content = $db->real_escape_string($doc->content);

            $res = $db->query("SELECT date FROM docs WHERE post_id = $doc->id");
            $row = $res->fetch_assoc();
            if (empty($row['date']) || $row['date'] == $doc->modified) {
                $db->query("INSERT INTO docs (post_id, title, url, date, content) VALUES ('$doc->id', '$doc->title', '$doc->url', '$doc->modified', '$content')");
                $tdata['new']++;
            } else {
                $db->query("UPDATE docs SET title = '$doc->title', url = '$doc->url', date = '$doc->modified', content = '$content' WHERE post_id = '$doc->id'");
                $tdata['update']++;
            }
        }
    }

    $db->query('TRUNCATE TABLE indexes');

    $res = $db->query('SELECT * FROM docs ORDER BY id DESC LIMIT 10');
    $tdata['count'] = $res->num_rows;
    while ($row = $res->fetch_assoc()) {
        $text = html_entity_decode($row['content']);
        $text = preg_replace('/[^a-zA-Z]+/i', ' ', strtolower(strip_tags($text)));

        $words = explode(' ', trim($text));
        $terms = Flight::stemming(Flight::stoplist($words));

        foreach ($terms as $term) {
            $resc = $db->query("SELECT count FROM indexes WHERE term = '$term' AND doc_id = $row[id]");
            if ($resc->num_rows > 0) {
                $rowc = $resc->fetch_assoc();
                $rowc['count']++;

                $db->query("UPDATE indexes SET count = $rowc[count] WHERE term = '$term' AND doc_id = $row[id]");
            } else {
                $db->query("INSERT INTO indexes (doc_id, term, count) VALUES ($row[id], '$term', 1)");
            }
        }
    }

    $resc2 = $db->query('SELECT COUNT(*) FROM indexes');
    $rowc2 = $resc2->fetch_row();
    $tdata['term_count'] = $rowc2[0];

    $tdata['time'] = round(microtime(true) - $start, 2);

    // Render page
    Flight::render('create_index', $tdata);
});

// Route show index
Flight::route('/index/show', function() {
    // Render page
    Flight::render('show_index');
});

// Route corpus
Flight::route('/corpus', function() {
    // Render page
    Flight::render('show_corpus');
});

// Route weighting
Flight::route('/weighting', function() {
    $start = microtime(true);

    $db = Flight::db();

    $data = [];

    $resn = $db->query('SELECT DISTINCT doc_id FROM indexes');
    $n = $resn->num_rows;

    $res = $db->query('SELECT * FROM indexes ORDER BY id');
    $data['count'] = $res->num_rows;

    while ($row = $res->fetch_assoc()) {
        $term = $row['term'];
        $tf = $row['count'];

        $resNTerm = $db->query("SELECT COUNT(*) as N FROM indexes WHERE term = '$term'");
        $rowNTerm = $resNTerm->fetch_assoc();
        $nTerm = $rowNTerm['N'];

        $w = $tf * log($n / $nTerm);

        $db->query("UPDATE indexes SET weight = $w WHERE id = $row[id]");
    }

    $data['time'] = round(microtime(true) - $start, 2);

    // Render page
    Flight::render('weighting', $data);
});

// Route count vector
Flight::route('/vector/count', function() {
    $start = microtime(true);

    $db = Flight::db();

    $data = [];

    $db->query('TRUNCATE TABLE vector');

    $res = $db->query('SELECT DISTINCT doc_id FROM indexes');
    $data['count'] = $res->num_rows;

    while ($row = $res->fetch_assoc()) {
        $id = $row['doc_id'];

        $resVec = $db->query("SELECT weight FROM indexes WHERE doc_id = $id");

        $vectorLen = 0;
        while ($rowVec = $resVec->fetch_assoc()) {
            $vectorLen = $vectorLen + $rowVec['weight'] * $rowVec['weight'];	
        }

        $vectorLen = sqrt($vectorLen);

        $db->query("INSERT INTO vector (doc_id, length) VALUES ($id, $vectorLen)");
    }

    $data['time'] = round(microtime(true) - $start, 2);

    // Render page
    Flight::render('count_vector', $data);
});

// Route corpus
Flight::route('/vector/show', function() {
    // Render page
    Flight::render('show_vector');
});

// Route retrieval
Flight::route('/retrieval', function() {
    $data = [
        'keyword' => Flight::request()->query->keyword
    ];
    // Render page
    Flight::render('retrieval', $data);
});

// Route cache
Flight::route('/cache', function() {
    // Render page
    Flight::render('cache');
});

// Route clear cache
Flight::route('/cache/clear', function() {
    $db = Flight::db();

    $db->query('TRUNCATE TABLE cache');

    Flight::redirect('/cache');
});

// Route ajax retrieval
Flight::route('/ajax/retrieval', function() {
    $start = microtime(true);

    $db = Flight::db();
 
    $keyword = Flight::request()->query->keyword;
    $limit = 10;
    $page = Flight::request()->query->page ?: 1;
    $offset = ($page - 1) * $limit;

    $data = [
        'docs' => []
    ];
    
    if ($keyword) {
        $resc = $db->query("SELECT COUNT(*) FROM cache WHERE query = '$keyword'");
        $rowc = $resc->fetch_row();
        if ($rowc[0] == 0) {
            Flight::countSimilarities($keyword);
        }

        $res = $db->query("SELECT * FROM cache WHERE query = '$keyword' ORDER BY value DESC LIMIT $limit OFFSET $offset");
        while ($row = $res->fetch_assoc()) {
            $id = $row['doc_id'];
            $sim = $row['value'];

            if ($id != 0) {
                $resd = $db->query("SELECT * FROM docs WHERE id = $id");
                $rowd = $resd->fetch_assoc();

                $data['docs'][] = [
                    'title' => html_entity_decode($rowd['title']),
                    'url' => $rowd['url'],
                    'content' => substr(strip_tags(html_entity_decode($rowd['content'])), 0, 500),
                    'similarity' => $sim
                ];
            }
        }
    }

    $data['time'] = round(microtime(true) - $start, 2);

    Flight::json($data);
});

// Route ajax corpus
Flight::route('/ajax/corpus', function() {
    $db = Flight::db();

    $limit = 10;
    $page = Flight::request()->query->page ?: 1;
    $offset = ($page - 1) * $limit;

    $res = $db->query("SELECT id, title, url FROM docs ORDER BY id DESC LIMIT $limit OFFSET $offset");
    $data = [];
    while ($row = $res->fetch_assoc()) {
        $t = html_entity_decode($row['title']);
        $row['title'] = $t;
        array_push($data, $row);
    }

    Flight::json($data);
});

// Route ajax index
Flight::route('/ajax/index', function() {
    $db = Flight::db();

    $limit = 10;
    $page = Flight::request()->query->page ?: 1;
    $offset = ($page - 1) * $limit;

    $res = $db->query("SELECT * FROM indexes ORDER BY id LIMIT $limit OFFSET $offset");
    $data = [];
    while ($row = $res->fetch_assoc()) {
        array_push($data, $row);
    }

    Flight::json($data);
});

// Route ajax vector
Flight::route('/ajax/vector', function() {
    $db = Flight::db();

    $limit = 10;
    $page = Flight::request()->query->page ?: 1;
    $offset = ($page - 1) * $limit;

    $res = $db->query("SELECT * FROM vector ORDER BY doc_id LIMIT $limit OFFSET $offset");
    $data = [];
    while ($row = $res->fetch_assoc()) {
        array_push($data, $row);
    }

    Flight::json($data);
});

// Route ajax cache
Flight::route('/ajax/cache', function() {
    $db = Flight::db();

    $limit = 10;
    $page = Flight::request()->query->page ?: 1;
    $offset = ($page - 1) * $limit;

    $res = $db->query("SELECT * FROM cache ORDER BY id LIMIT $limit OFFSET $offset");
    $data = [];
    while ($row = $res->fetch_assoc()) {
        array_push($data, $row);
    }

    Flight::json($data);
});

// Route index
Flight::route('/', function() {
    // Render page
    Flight::render('index');
});

// Start flight
Flight::start();
