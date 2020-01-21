use logigator;

insert into logigator.users (pk_id, username, password, email, login_type, profile_image) VALUES (1000, 'system', '', 'support@logigator.com', 'local', null);

INSERT INTO logigator.projects (pk_id, location, name, description, fk_user, fk_originates_from, last_edited, created_on, is_component, symbol, num_inputs, num_outputs, labels) VALUES (1000, '9251f9b3-15ae-4f6e-a32d-0d637edb2ce0', 'Basic Gates', '', 1000, null, '2020-01-21 20:21:02', '2020-01-21 20:21:02', false, null, null, null, null);
INSERT INTO logigator.projects (pk_id, location, name, description, fk_user, fk_originates_from, last_edited, created_on, is_component, symbol, num_inputs, num_outputs, labels) VALUES (1001, 'd01148d7-7b80-4cb1-b257-a9ced3085a9e', 'Half Adder', '', 1000, null, '2020-01-21 20:31:32', '2020-01-21 20:24:13', false, null, null, null, null);
INSERT INTO logigator.projects (pk_id, location, name, description, fk_user, fk_originates_from, last_edited, created_on, is_component, symbol, num_inputs, num_outputs, labels) VALUES (1002, '161e0526-3eb5-4ede-8654-314f4d67255d', 'Full Adder', '', 1000, null, '2020-01-21 20:31:01', '2020-01-21 20:31:01', false, null, null, null, null);
INSERT INTO logigator.projects (pk_id, location, name, description, fk_user, fk_originates_from, last_edited, created_on, is_component, symbol, num_inputs, num_outputs, labels) VALUES (1003, '88b4d774-d94f-4bcd-bac2-49d81f5465c1', 'Flip Flops', '', 1000, null, '2020-01-21 20:34:15', '2020-01-21 20:34:15', false, null, null, null, null);
INSERT INTO logigator.projects (pk_id, location, name, description, fk_user, fk_originates_from, last_edited, created_on, is_component, symbol, num_inputs, num_outputs, labels) VALUES (1004, 'bc65d4dd-1777-4841-a906-97f1010068b7', '4 Bit Counter', '', 1000, null, '2020-01-21 21:39:36', '2020-01-21 21:39:35', false, null, null, null, null);
INSERT INTO logigator.projects (pk_id, location, name, description, fk_user, fk_originates_from, last_edited, created_on, is_component, symbol, num_inputs, num_outputs, labels) VALUES (1005, '9c3e4df9-e98c-4756-a107-b181b8fe25ed', '4 Bit Adder', '', 1000, null, '2020-01-21 21:51:58', '2020-01-21 21:47:13', false, null, null, null, null);
INSERT INTO logigator.projects (pk_id, location, name, description, fk_user, fk_originates_from, last_edited, created_on, is_component, symbol, num_inputs, num_outputs, labels) VALUES (1006, 'c116eab3-398a-4a1d-ba2c-2d53ab4ce30c', 'Custom Full Adder', '', 1000, null, '2020-01-21 21:49:50', '2020-01-21 21:47:41', true, 'FA*', 3, 2, 'A;B;Cin;S;C');

insert into logigator.links (pk_id, address, is_public, fk_project) values (1000, '399260d2-70ea-4d96-849c-96905a9d3e3d', true, 1000);
insert into logigator.links (pk_id, address, is_public, fk_project) values (1001, '0dabb6bf-442d-467f-8f73-cd83957afc3f', true, 1001);
insert into logigator.links (pk_id, address, is_public, fk_project) values (1002, 'e1949e6c-b65d-4020-8d9e-f4f90780f052', true, 1002);
insert into logigator.links (pk_id, address, is_public, fk_project) values (1003, 'e7185d88-75bd-4590-b525-c498b1c30b53', true, 1003);
insert into logigator.links (pk_id, address, is_public, fk_project) values (1004, 'd178024f-113e-496f-a9a0-750740a82ae7', true, 1004);
insert into logigator.links (pk_id, address, is_public, fk_project) values (1005, 'bc6d296a-8251-4016-a4c0-555464ed1358', true, 1005);
