<?php
/**
 * ToAccountEnds.php
 * Copyright (C) 2016 thegrumpydictator@gmail.com
 *
 * This software may be modified and distributed under the terms
 * of the MIT license.  See the LICENSE file for details.
 */

declare(strict_types = 1);

namespace FireflyIII\Rules\Triggers;

use FireflyIII\Models\Account;
use FireflyIII\Models\TransactionJournal;

/**
 * Class ToAccountEnds
 *
 * @package FireflyIII\Rules\Triggers
 */
final class ToAccountEnds extends AbstractTrigger implements TriggerInterface
{

    /**
     * A trigger is said to "match anything", or match any given transaction,
     * when the trigger value is very vague or has no restrictions. Easy examples
     * are the "AmountMore"-trigger combined with an amount of 0: any given transaction
     * has an amount of more than zero! Other examples are all the "Description"-triggers
     * which have hard time handling empty trigger values such as "" or "*" (wild cards).
     *
     * If the user tries to create such a trigger, this method MUST return true so Firefly III
     * can stop the storing / updating the trigger. If the trigger is in any way restrictive
     * (even if it will still include 99.9% of the users transactions), this method MUST return
     * false.
     *
     * @param null $value
     *
     * @return bool
     */
    public static function willMatchEverything($value = null)
    {
        if (!is_null($value)) {
            return strval($value) === '';
        }

        return true;
    }

    /**
     * @param TransactionJournal $journal
     *
     * @return bool
     */
    public function triggered(TransactionJournal $journal): bool
    {
        $toAccountName = '';

        /** @var Account $account */
        foreach (TransactionJournal::destinationAccountList($journal) as $account) {
            $toAccountName .= strtolower($account->name);
        }

        $toAccountNameLength = strlen($toAccountName);
        $search              = strtolower($this->triggerValue);
        $searchLength        = strlen($search);

        // if the string to search for is longer than the account name,
        // shorten the search string.
        if ($searchLength > $toAccountNameLength) {
            $search       = substr($search, ($toAccountNameLength * -1));
            $searchLength = strlen($search);
        }


        $part = substr($toAccountName, $searchLength * -1);

        if ($part == $search) {
            return true;
        }

        return false;
    }
}
