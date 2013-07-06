USE `appbase`;

-- Setting up access --

-- Groups request objects --
SELECT @SuperuserGroup := id FROM groups WHERE title = 'superuser';
INSERT INTO `aro` (`id`, `object_id`, `table_name`) VALUES
  (NULL, @SuperuserGroup, 'groups');
SELECT @ARO_SuperuserGroup := id FROM aro WHERE object_id = @SuperuserGroup AND table_name = 'groups';

SELECT @UserGroup := id FROM groups WHERE title = 'user';
INSERT INTO `aro` (`id`, `object_id`, `table_name`) VALUES
  (NULL, @UserGroup, 'groups');
SELECT @ARO_UserGroup := id FROM aro WHERE object_id = @UserGroup AND table_name = 'groups';

-- Permissions control objects --
INSERT INTO `aco` (`id`, `name`, `default_access`) VALUES 
  (NULL, 'permissions', 0), 
  (NULL, 'permissions/index', 0);
SELECT @ACO_Permissions := id FROM aco WHERE name = 'permissions';
SELECT @ACO_PermissionsIndex := id FROM aco WHERE name = 'permissions/index';

-- Users control objects --
INSERT INTO `aco` (`id`, `name`, `default_access`) VALUES 
  (NULL, 'users', 0), 
  (NULL, 'users/index', 0),
  (NULL, 'users/save', 0),
  (NULL, 'users/delete', 0),
  (NULL, 'users/switch', 0);
SELECT @ACO_Users := id FROM aco WHERE name = 'users';
SELECT @ACO_UsersIndex := id FROM aco WHERE name = 'users/index';
SELECT @ACO_UsersSave := id FROM aco WHERE name = 'users/save';
SELECT @ACO_UsersDelete := id FROM aco WHERE name = 'users/delete';
SELECT @ACO_UsersSwitch := id FROM aco WHERE name = 'users/switch';

-- Groups control objects --
INSERT INTO `aco` (`id`, `name`, `default_access`) VALUES 
  (NULL, 'groups', 0), 
  (NULL, 'groups/index', 0),
  (NULL, 'groups/save', 0),
  (NULL, 'groups/delete', 0),
  (NULL, 'groups/view', 0);
SELECT @ACO_Groups := id FROM aco WHERE name = 'groups';
SELECT @ACO_GroupsIndex := id FROM aco WHERE name = 'groups/index';
SELECT @ACO_GroupsSave := id FROM aco WHERE name = 'groups/save';
SELECT @ACO_GroupsDelete := id FROM aco WHERE name = 'groups/delete';
SELECT @ACO_GroupsView := id FROM aco WHERE name = 'groups/view';

-- Superuser group permissions --

-- for permissions control objects --
INSERT INTO `permissions` (`id`,`aco_id`,`aro_id`,`access`) VALUES
  (NULL, @ACO_Permissions, @ARO_SuperuserGroup, 1),
  (NULL, @ACO_PermissionsIndex, @ARO_SuperuserGroup, 1);
-- for users control objects --
INSERT INTO `permissions` (`id`,`aco_id`,`aro_id`,`access`) VALUES
  (NULL, @ACO_Users, @ARO_SuperuserGroup, 1),
  (NULL, @ACO_UsersIndex, @ARO_SuperuserGroup, 1),
  (NULL, @ACO_UsersSave, @ARO_SuperuserGroup, 1),
  (NULL, @ACO_UsersDelete, @ARO_SuperuserGroup, 1),
  (NULL, @ACO_UsersSwitch, @ARO_SuperuserGroup, 1);
-- for groups control objects --
INSERT INTO `permissions` (`id`,`aco_id`,`aro_id`,`access`) VALUES
  (NULL, @ACO_Groups, @ARO_SuperuserGroup, 1),
  (NULL, @ACO_GroupsIndex, @ARO_SuperuserGroup, 1),
  (NULL, @ACO_GroupsSave, @ARO_SuperuserGroup, 1),
  (NULL, @ACO_GroupsDelete, @ARO_SuperuserGroup, 1),
  (NULL, @ACO_GroupsView, @ARO_SuperuserGroup, 1);
