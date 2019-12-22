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

insert into projects (
    name,
    is_component,
    fk_user,
    description,
    location
) values (
    'testProject',
    false,
    1000,
    'description',
    'test-project-location'
);

