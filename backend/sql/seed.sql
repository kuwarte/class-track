USE class_tracker;

INSERT INTO subjects (name) VALUES ('Math'), ('Science'), ('History');

INSERT INTO learning_entries (subject_id, topic, notes, date)
VALUES
(1, 'Algebra', 'Learned quadratic equations', '2026-02-08'),
(2, 'Physics', 'Newton Laws review', '2026-02-08');

