-- #! mysql
-- #{ initialization
CREATE TABLE IF NOT EXISTS warp
(
    name                VARCHAR(50) NOT NULL PRIMARY KEY COMMENT 'warp name',
    server_name         VARCHAR(50) NOT NULL COMMENT 'server where warp exist',
    x                   FLOAT       NOT NULL COMMENT 'position x',
    y                   FLOAT       NOT NULL COMMENT 'position y',
    z                   FLOAT       NOT NULL COMMENT 'position z',
    yaw                 FLOAT       NOT NULL COMMENT 'yaw',
    pitch               FLOAT       NOT NULL COMMENT 'pitch',
    is_title            BOOLEAN DEFAULT true COMMENT 'send title',
    is_particle         BOOLEAN DEFAULT true COMMENT 'send particle',
    is_sound            BOOLEAN DEFAULT true COMMENT 'send sound',
    is_permit           BOOLEAN DEFAULT true COMMENT 'is normal player able to use',
    is_command_register BOOLEAN DEFAULT true COMMENT 'is it able to be used through command'
) CHARSET = utf8mb4 comment ='warps data';
-- #}
-- #{ warp
-- #    { add
-- #        :name string
-- #        :server_name string
-- #        :x float
-- #        :y float
-- #        :z float
-- #        :yaw float
-- #        :pitch float
-- #        :is_title bool
-- #        :is_particle bool
-- #        :is_sound bool
-- #        :is_permit bool
-- #        :is_command_register bool
INSERT IGNORE INTO warp(name,
                        server_name,
                        x,
                        y,
                        z,
                        yaw,
                        pitch,
                        is_title,
                        is_particle,
                        is_sound,
                        is_permit,
                        is_command_register)
VALUES (:name,
        :server_name,
        :x,
        :y,
        :z,
        :yaw,
        :pitch,
        :is_title,
        :is_particle,
        :is_sound,
        :is_permit,
        :is_command_register);
-- #    }
-- #    { edit
-- #        :name string
-- #        :is_title bool
-- #        :is_particle bool
-- #        :is_sound bool
-- #        :is_permit bool
-- #        :is_command_register bool
UPDATE warp
SET is_title            = :is_title,
    is_particle         = :is_particle,
    is_sound            = :is_sound,
    is_permit           = :is_permit,
    is_command_register = :is_command_register
WHERE name = :name;
-- #    }
-- #    { remove
-- #        :name string
DELETE
FROM warp
WHERE name = :name;
-- #    }
-- #    { get
-- #        :name string
SELECT * FROM warp WHERE name = :name LIMIT 1;
-- #    }
-- #    { get.all
SELECT * FROM warp;
-- #    }
-- #}
