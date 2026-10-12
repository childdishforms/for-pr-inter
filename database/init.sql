CREATE TABLE contacts (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    name TEXT NOT NULL,
    phone TEXT NOT NULL,
    email TEXT DEFAULT NULL,
    note TEXT DEFAULT NULL
);

INSERT INTO contacts (id, name, phone, email, note) VALUES
    (1, 'Анна Смирнова', '+7 900 123-45-67', 'anna@dsa.com', 'Рабочий контакт'),
    (2, 'Иван Петров', '+7 (000) 000-00-02', 'ivan@example.com', NULL),
    (3, 'Мария Орлова', '+7 (000) 000-00-03', NULL, 'Позвонить вечером'),
    (4, 'Дмитрий Соколов', '+7 (000) 000-00-04', 'dmitry@example.com', 'Коллега'),
    (5, 'Елена Кузнецова', '+7 (000) 000-00-05', NULL, NULL);
