
SELECT u.ID, u.display_name um.yog
FROM wp_users AS u 
LEFT JOIN wp_usermeta AS um1 ON u.ID = um1.user_id 
LEFT JOIN wp_usermeta AS um2 ON u.ID = um2.user_id 
WHERE  um1.meta_key = 'key1' AND um1.meta_value = 'value1' 
AND um2.meta_key = 'keyA' AND um2.meta_value = 'valueA'
LIMIT 0, 60


// all the pnms
SELECT user_id FROM wp_usermeta WHERE meta_key='ifcrush_role' and meta_value='pnm'

// get all the pnms
select um1.user_id, 
um1.meta_value as netID, 
um2.meta_value as last_name,
um3.meta_value as first_name
from wp_usermeta as um1 
left join wp_usermeta as um2 on um1.user_id = um2.user_id 
left join wp_usermeta as um3 on um1.user_id = um3.user_id 
where   
um3.meta_key='first_name' AND 
um2.meta_key='last_name' AND 
um1.meta_key='ifcrush_netID' AND
um1.user_id IN (SELECT user_id FROM wp_usermeta WHERE meta_key='ifcrush_role' and meta_value='pnm')
order by netID

// get all the frats, letters and fullname
select 
um1.meta_value as ifcrush_frat_letters, 
um2.meta_value as ifcrush_frat_fullname
from wp_usermeta as um1 
left join wp_usermeta as um2 on um1.user_id = um2.user_id 
where   
um1.meta_key='ifcrush_frat_letters' AND 
um2.meta_key='ifcrush_frat_fullname' AND 
um1.user_id IN (SELECT user_id FROM wp_usermeta WHERE meta_key='ifcrush_role' and meta_value='rc')
order by ifcrush_frat_fullname