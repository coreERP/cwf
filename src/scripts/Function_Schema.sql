Create Function sys.fn_get_menu_id_by_name(pmenu_name varchar(250)) 
Returns BigInt
As $BODY$
Declare
	vMenu_id BigInt;
Begin
	Select menu_id into vMenu_id
	From sys.menu
	where menu_name=pmenu_name;

	If vMenu_id is null Then
		vMenu_id:=-1;
	End If;
	Return vMenu_id;

End;
$BODY$
  Language 'plpgsql';

?==?
Create Function sys.sp_get_menu_key(pparent_menu_name Varchar(100)) 
Returns Varchar(10) 
As
$BODY$
Declare
	vMenu_key Varchar(10) = ''; vMenu_keycode Varchar(1) = ''; vSeq BigInt = 0;
	vParent_menu_id BigInt = 0; vParent_menu_key Varchar(10) = ''; 
Begin
	-- Find parent menu key
	Select menu_id, menu_key Into vParent_menu_id, vParent_menu_key
	From sys.menu
	Where menu_name=pparent_menu_name;

	-- Find key code of new menu code
	If (vParent_menu_key != '') Then
		vMenu_keycode := chr(ascii(left(vParent_menu_key, 1))+1);
	Else
		vMenu_keycode := 'A';
	End If;

	-- Insert the sequence if required
	If Not Exists(Select * From sys.menu_seq Where menu_level=vMenu_keycode) Then
		Insert Into sys.menu_seq (menu_level, max_id)
		Values(vMenu_keycode, 0);
	End if;

	-- Generate new key
	SELECT max_id + 1 INTO vSeq
	FROM sys.menu_seq 
	WHERE menu_level = vMenu_keycode;
	
	UPDATE sys.menu_seq
	SET max_id = vSeq
	WHERE menu_level = vMenu_keycode;

	-- create the menu_key
	vMenu_key := vMenu_keycode || lpad(cast(vSeq as text), 4, '0');

Return vMenu_key;

End
$BODY$
Language plpgsql;

?==?