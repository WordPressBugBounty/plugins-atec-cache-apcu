	<?php
	echo 'TEST WP CACHE<br>';
//	echo '# DEL '.wp_cache_delete('TEST').'#<br>';

	wp_cache_add_non_persistent_groups('TEST');
	var_dump(wp_cache_get_np_groups());
	echo '<br>';
	//echo '# SET TEST '.wp_cache_set('TEST','TEST','TEST').'#<br>';
	echo '# GET TEST '.wp_cache_get('TEST','TEST').'#<br>';

	if (false)
	{

		echo '# SET TEST '.wp_cache_set('TEST','TEST').'#<br>';
		echo '# GET TEST '.wp_cache_get('TEST').'#<br>';
		echo '# wp_cache_flush_runtime ';
		wp_cache_flush_runtime();
		echo '#<br>';
		echo '# GET TEST '.wp_cache_get('TEST').'#<br>';
	
		echo '# SET TEST '.wp_cache_set('TEST','TEST','TEST').'#<br>';
		echo '# GET TEST '.wp_cache_get('TEST','TEST').'#<br>';
		echo '# wp_cache_flush_group ';
		wp_cache_flush_group('TEST');
		echo '#<br>';
		echo '# GET TEST '.wp_cache_get('TEST','TEST').'#<br>';
	}

	if (false)
	{
	echo '# ADD '.wp_cache_add('TEST_INC','TEST').'#<br>';
	echo '# GET '.wp_cache_get('TEST_INC').'#<br>';
	echo '# ADD '.wp_cache_add('TEST_INC','TEST').'#<br>';

	echo '# DEL '.wp_cache_delete('TEST_INC').'#<br>';

	echo '# SET '.wp_cache_set('TEST_INC','TEST').'#<br>';
	echo '# GET '.wp_cache_get('TEST_INC').'#<br>';
	echo '# SET '.wp_cache_set('TEST_INC','TEST').'#<br>';
	}

	if (false)
	{
	echo '# del_multiple ';
	wp_cache_delete_multiple(['TEST_M1','TEST_M2']);
	echo '#<br>';

	echo '# add_multiple ';
	var_dump(wp_cache_add_multiple(['TEST_M1'=>'TEST_M1','TEST_M2'=>'TEST_M2']));
	echo '#<br>';
	echo '# GET TEST_M1 '.wp_cache_get('TEST_M1').'#<br>';
	echo '# GET TEST_M2 '.wp_cache_get('TEST_M2').'#<br>';

	echo '# add_multiple ';
	var_dump(wp_cache_add_multiple(['TEST_M1'=>'TEST_M1','TEST_M2'=>'TEST_M2']));
	echo '#<br>';
	echo '# GET TEST_M1 '.wp_cache_get('TEST_M1').'#<br>';
	echo '# GET TEST_M2 '.wp_cache_get('TEST_M2').'#<br>';

	echo '# del_multiple ';
	wp_cache_delete_multiple(['TEST_M1','TEST_M2']);
	echo '#<br>';

	echo '# set_multiple ';
	var_dump(wp_cache_set_multiple(['TEST_M1'=>'TEST_M1','TEST_M2'=>'TEST_M2']));
	echo '#<br>';
	echo '# GET TEST_M1 '.wp_cache_get('TEST_M1').'#<br>';
	echo '# GET TEST_M2 '.wp_cache_get('TEST_M2').'#<br>';

	echo '# set_multiple ';
	var_dump(wp_cache_set_multiple(['TEST_M1'=>'TEST_M1','TEST_M2'=>'TEST_M2']));
	echo '#<br>';
	echo '# GET TEST_M1 '.wp_cache_get('TEST_M1').'#<br>';
	echo '# GET TEST_M2 '.wp_cache_get('TEST_M2').'#<br>';

	echo '# replace ';
	var_dump(wp_cache_replace('TEST_M1','TEST_C1'));
	echo '#<br>';
	echo '# GET TEST_M1 '.wp_cache_get('TEST_M1').'#<br>';
	echo '# GET TEST_M2 '.wp_cache_get('TEST_M2').'#<br>';

	echo '# del_multiple ';
	wp_cache_delete_multiple(['TEST_M1','TEST_M2']);
	echo '#<br>';
	}

    if (false)
    {
	echo '# INCR '.wp_cache_incr('TEST_INC').'#<br>';
	echo '# SET TEST '.wp_cache_set('TEST_INC','TEST').'#<br>';
	echo '# INCR TEST '.wp_cache_incr('TEST_INC').'#<br>';

	echo '# SET 0 '.wp_cache_set('TEST_INC',0).'#<br>';
	echo '# INCR 0 '.wp_cache_incr('TEST_INC').'#<br>';

	echo '# SET -1 '.wp_cache_set('TEST_INC',-1).'#<br>';
	echo '# INCR -1 '.wp_cache_incr('TEST_INC').'#<br>';

	echo '# SET -2 '.wp_cache_set('TEST_INC',-2).'#<br>';
	echo '# INCR -2 '.wp_cache_incr('TEST_INC').'#<br>';

	echo '# DEL '.wp_cache_delete('TEST_INC').'#<br>';

	echo '# DECR '.wp_cache_decr('TEST_INC').'#<br>';

	echo '# SET TEST '.wp_cache_set('TEST_INC','TEST').'#<br>';
	echo '# DECR TEST '.wp_cache_decr('TEST_INC').'#<br>';

	echo '# SET 0 '.wp_cache_set('TEST_INC',0).'#<br>';
	echo '# DECR 0 '.wp_cache_decr('TEST_INC').'#<br>';

	echo '# SET -1 '.wp_cache_set('TEST_INC',-1).'#<br>';
	echo '# DECR -1 '.wp_cache_decr('TEST_INC').'#<br>';

	echo '# SET -2 '.wp_cache_set('TEST_INC',-2).'#<br>';
	echo '# DECR -2 '.wp_cache_decr('TEST_INC').'#<br>';

	echo '# SET 2 '.wp_cache_set('TEST_INC',2).'#<br>';
	echo '# DECR 2 '.wp_cache_decr('TEST_INC').'#<br>';
    }