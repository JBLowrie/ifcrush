
SELECT u.ID, u.display_name um.yog
FROM wp_users AS u 
LEFT JOIN wp_usermeta AS um1 ON u.ID = um1.user_id 
LEFT JOIN wp_usermeta AS um2 ON u.ID = um2.user_id 
WHERE  um1.meta_key = 'key1' AND um1.meta_value = 'value1' 
AND um2.meta_key = 'keyA' AND um2.meta_value = 'valueA'
LIMIT 0, 60
