<?php

namespace RedUNIT\Base;

use RedUNIT\Base as Base;
use RedBeanPHP\Facade as R;

/**
 * Hybrid
 *
 * Test Hybrid mode where the database can be unfozen
 * in case of an exception during storing.
 *
 * @file    RedUNIT/Base/Hybrid.php
 * @desc    Tests hybrid mode
 * @author  Gabor de Mooij and the RedBeanPHP Community
 * @license New BSD/GPLv2
 *
 * (c) G.J.G.T. (Gabor) de Mooij and the RedBeanPHP Community.
 * This source file is subject to the New BSD/GPLv2 License that is bundled
 * with this source code in the file license.txt.
 */
class Hybrid extends Base
{
	/**
	 * Tests hybrid mode.
	 *
	 * @return void
	 */
	public function testHybrid()
	{
		R::nuke();
		$book = R::dispense('book');
		$book->pages = 123;
		$id = R::store( $book );
		R::freeze( TRUE );
		R::setAllowHybridMode( FALSE );
		$book->title = 'Tales of a misfit';
		try {
			R::store( $book );
			fail();
		} catch(\Exception $e) {
			pass();
		}
		$book = $book->fresh();
		asrt( is_null( $book->title ), TRUE );
		R::setAllowHybridMode( TRUE );
		$book->title = 'Tales of a misfit';
		try {
			R::store( $book );
			pass();
		} catch(\Exception $e) {
			fail();
		}
		$book = $book->fresh();
		asrt( $book->title, 'Tales of a misfit' );
		R::setAllowHybridMode( FALSE );
		R::freeze( FALSE );
	}

	/**
	 * Other exceptions must fall through...
	 */
	public function testHybridNonSQLException()
	{
		R::nuke();
		$toy = R::dispense('brokentoy');
		R::freeze( TRUE );
		R::setAllowHybridMode( TRUE );
		try {
			R::store( $toy );
			fail();
		} catch(\Exception $e) {
			pass();
		}
		R::setAllowHybridMode( FALSE );
		R::nuke();
		$toy = R::dispense('toy');
		R::freeze( TRUE );
		R::setAllowHybridMode( TRUE );
		try {
			R::store( $toy );
			pass();
		} catch(\Exception $e) {
			fail();
		}
		R::setAllowHybridMode( FALSE );
	}
	
	/**
	 * Test whether Hybrid mode is only activated
	 * for latest or 5.4 without novice and ensure
	 * maintaining backward compatibility by not setting
	 * Hybrid allowed for 5.3 and earlier.
	 */
	 public function testVersions()
	 {
		R::useFeatureSet('novice/latest');
		asrt( R::setAllowHybridMode( FALSE ), FALSE );
		R::useFeatureSet('latest');
		asrt( R::setAllowHybridMode( FALSE ), TRUE );
		R::useFeatureSet('novice/5.4');
		asrt( R::setAllowHybridMode( FALSE ), FALSE );
		R::useFeatureSet('5.4');
		asrt( R::setAllowHybridMode( FALSE ), TRUE );
		R::useFeatureSet('novice/5.3');
		asrt( R::setAllowHybridMode( FALSE ), FALSE );
		R::useFeatureSet('5.3');
		asrt( R::setAllowHybridMode( FALSE ), FALSE );
	 }
}

