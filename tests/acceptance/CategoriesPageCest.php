<?php

class CategoriesPageCest
{
	public function CategoriesPageWorks(AcceptanceTester $I)
	{
		$I->amOnPage('/categories');
		$I->see('Simple Forums');
	}

}
