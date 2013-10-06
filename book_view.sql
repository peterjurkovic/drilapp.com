CREATE VIEW `book_view` AS 
SELECT `book`.`_id` AS `_id`, `book`.`name` AS `name`, `book`.`id_user` as `id_user`
	, `book`.`lang` AS `lang`, `book`.`lang_a` AS `lang_a`, `book`.`level` AS
	`level`, `book`.`descr` AS `descr`, `book`.`author` AS
	`author`, `book`.`import_id` AS `import_id`, `book`.`email` AS `email`, `book`.`create` AS `create`,
	`book`.`downloads` AS `downloads`, `lang_answer`.`name_sk` AS `lang_answer`,
	`lang_question`.`name_sk` AS `lang_question`, count(`words`.`token`) AS `count`,
	`l`.`name` AS `level_name`, `book`.`shared` AS `shared`
FROM `import_book` `book` 
JOIN `lang` `lang_question` ON `book`.`lang` = `lang_question`.`id_lang`
JOIN `lang` `lang_answer` ON `book`.`lang_a` = `lang_answer`.`id_lang`
JOIN `level` `l` ON `book`.`level` = `l`.`id_level`
LEFT JOIN `import_word` `words` ON `book`.`import_id` = `words`.`token`
GROUP BY `book`.`_id` 
ORDER BY `book`.`_id` DESC 
