CREATE TABLE `cache` (
  `id` int(11) NOT NULL,
  `doc_id` int(11) NOT NULL,
  `query` varchar(255) NOT NULL,
  `value` float NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `docs` (
  `id` int(11) NOT NULL,
  `post_id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `url` varchar(255) NOT NULL,
  `date` datetime NOT NULL,
  `content` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ROW_FORMAT=COMPACT;

CREATE TABLE `indexes` (
  `id` int(11) NOT NULL,
  `doc_id` int(11) NOT NULL,
  `term` varchar(50) NOT NULL,
  `count` int(11) NOT NULL,
  `weight` float NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `vector` (
  `doc_id` int(11) NOT NULL,
  `length` float NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


ALTER TABLE `cache`
  ADD PRIMARY KEY (`id`),
  ADD KEY `doc_id` (`doc_id`);

ALTER TABLE `docs`
  ADD PRIMARY KEY (`id`);

ALTER TABLE `indexes`
  ADD PRIMARY KEY (`id`),
  ADD KEY `doc_id` (`doc_id`);

ALTER TABLE `vector`
  ADD KEY `doc_id` (`doc_id`);


ALTER TABLE `cache`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
ALTER TABLE `docs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
ALTER TABLE `indexes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
