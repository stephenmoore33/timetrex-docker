export var TTUUID = function() {
};

TTUUID.zero_id = '00000000-0000-0000-0000-000000000000';
TTUUID.not_exist_id = 'ffffffff-ffff-ffff-ffff-ffffffffffff';

TTUUID.counter = 0;

TTUUID.generateUUID = function( seed ) {
	if ( !seed && LocalCacheData && LocalCacheData.loginUser && LocalCacheData.loginUser.id ) {
		var user_id = LocalCacheData.loginUser.id.split( '-' );
		seed = user_id[1] + user_id[2] + user_id[3];
	}

	if ( seed == null || seed.length != 12 ) {
		seed = ( TTUUID.randomUI08() | 1 ) * 0x10000000000 + TTUUID.randomUI40();
	}

	var sequence = TTUUID.randomUI14();
	var node = seed;
	var tick = TTUUID.randomUI04();
	var timestamp = new Date().getTime();

	//Helps ensure no duplicate UUIDs in a tight loop.
	timestamp += TTUUID.counter;
	TTUUID.counter += 1;

	var tf = TTUUID.getTimeFieldValues( timestamp );
	var tl = tf.low + tick;
	var thav = ( tf.hi & 0xFFF ) | 0x1000;

	sequence &= 0x3FFF;
	var cshar = ( sequence >>> 8 ) | 0x80;
	var csl = sequence & 0xFF;

	return TTUUID.fromParts( tl, tf.mid, thav, cshar, csl, node );
};

TTUUID.castUUID = function( uuid ) {
	//allow nulls for cases where the column allows it.
	if ( uuid === null || TTUUID.isUUID( uuid ) == true ) {
		return uuid;
	}

	return TTUUID.zero_id;
};

TTUUID.isUUID = function( uuid ) {
	var regex = new RegExp( '[a-zA-Z0-9]{8}-[a-zA-Z0-9]{4}-[a-zA-Z0-9]{4}-[a-zA-Z0-9]{4}-[a-zA-Z0-9]{12}' );
	if ( uuid != '' && regex.test( uuid ) ) {
		return true;
	}

	return false;
};

TTUUID.fromParts = function( timeLow, timeMid, timeHiAndVersion, clockSeqHiAndReserved, clockSeqLow, node ) {
	var hex =
		TTUUID.paddedString( timeHiAndVersion.toString( 16 ), 4 ) + TTUUID.paddedString( timeMid.toString( 16 ), 4 )
		+ '-' + TTUUID.paddedString( timeLow.toString( 16 ).substring( 0, 4 ), 4 )
		+ '-' + TTUUID.paddedString( timeLow.toString( 16 ).substring( 5, 8 ), 4 )
		+ '-' + TTUUID.paddedString( clockSeqHiAndReserved.toString( 16 ), 2 ) + TTUUID.paddedString( clockSeqLow.toString( 16 ), 2 )
		+ '-' + node.toString().substring( 0, 12 );

	return hex;
};

TTUUID.maxFromBits = function( bits ) {
	return Math.pow( 2, bits );
};

TTUUID.limitUI04 = TTUUID.maxFromBits( 4 );
TTUUID.limitUI08 = TTUUID.maxFromBits( 8 );
TTUUID.limitUI14 = TTUUID.maxFromBits( 14 );
TTUUID.limitUI16 = TTUUID.maxFromBits( 16 );
TTUUID.limitUI40 = TTUUID.maxFromBits( 40 );

TTUUID.randomUI04 = function() {
	return TTUUID.getRandomInt( 0, ( TTUUID.limitUI04 - 1 ) );
};

TTUUID.randomUI08 = function() {
	return TTUUID.getRandomInt( 0, ( TTUUID.limitUI08 - 1 ) );
};

TTUUID.randomUI14 = function() {
	return TTUUID.getRandomInt( 0, ( TTUUID.limitUI14 - 1 ) );
};

TTUUID.randomUI40 = function() {
	return ( 0 | Math.random() * ( 1 << 30 ) ) + ( 0 | Math.random() * ( 1 << 40 - 30 ) ) * ( 1 << 30 );
};

TTUUID.getTimeFieldValues = function( time ) {
	var ts = time - Date.UTC( 1582, 9, 15 );
	var hm = ( ( ts / 0x100000000 ) * 10000 ) & 0xFFFFFFF;
	return { low: ( ( ts & 0xFFFFFFF ) * 10000 ) % 0x100000000, mid: hm & 0xFFFF, hi: hm >>> 16, timestamp: ts };
};

TTUUID.paddedString = function( string, length, z ) {
	string = String( string );
	z = ( !z ) ? '0' : z;
	var i = length - string.length;
	for ( ; i > 0; i >>>= 1, z += z ) {
		if ( i & 1 ) {
			string = z + string;
		}
	}
	return string;
};

TTUUID.getRandomInt = function( min, max ) {
	var random_num;

	var crypto_obj = window.crypto || window.msCrypto; // for IE 11
	if ( crypto_obj ) {
		var byte_array = new Uint8Array( 1 );
		crypto_obj.getRandomValues( byte_array );

		random_num = '0.' + byte_array[0].toString();
	} else {
		random_num = Math.random();
	}

	random_num = Math.floor( random_num * ( max - min + 1 ) ) + min;

	return random_num;
};