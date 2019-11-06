use logigator;

insert into users (
    username,
    password,
    email,
    login_type,
    profile_image
) VALUES (
    'test',
    '$2y$10$oB1tdXfSSaafSIcn7ucLq.ydBkYfRTw45R7jXzl.X0EQ8XMLOJvCu', -- test
    'test@test.com',
    'local',
    'abc'
);
