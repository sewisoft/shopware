<?php

namespace Shopware\Tests\Mink;

use Behat\Gherkin\Node\PyStringNode;
use Behat\Mink\Element\NodeElement;
use Behat\Mink\Exception\DriverException;
use Behat\Mink\Exception\ResponseTextException;
use Behat\Mink\WebAssert;
use SensioLabs\Behat\PageObjectExtension\PageObject\Page;
use Shopware\Tests\Mink\Page\Homepage;
use Shopware\Tests\Mink\Element\MultipleElement;

class SpecialContext extends SubContext
{
    /**
     * @Given /^the articles from "(?P<name>[^"]*)" have tax id (?P<num>\d+)$/
     */
    public function theArticlesFromHaveTaxId($supplier, $taxId)
    {
        $sql = sprintf(
            'UPDATE s_articles SET taxID = %d WHERE supplierID =
                (SELECT id FROM s_articles_supplier WHERE name = "%s")',
            $taxId,
            $supplier
        );

        $this->getService('db')->exec($sql);
    }

    /**
     * @Given /^I am on the (page "[^"]*")$/
     * @When /^I go to the (page "[^"]*")$/
     */
    public function iAmOnThePage(Page $page)
    {
        $page->open();
    }

    /**
     * @Then /^I should be on the (page "[^"]*")$/
     */
    public function iShouldBeOnThePage(Page $page)
    {
        $page->verifyPage();
    }

    /**
     * @Then /^I should see (?P<quantity>\d+) element of type "(?P<elementClass>[^"]*)"( eventually)?$/
     * @Then /^I should see (?P<quantity>\d+) elements of type "(?P<elementClass>[^"]*)"( eventually)?$/
     */
    public function iShouldSeeElementsOfType($count, $elementClass, $mode = null)
    {
        /** @var Homepage $page */
        $page = $this->getPage('Homepage');

        if ($mode === null) {
            $elements = $this->getMultipleElement($page, $elementClass);
            Helper::assertElementCount($elements, $count);
            return;
        }

        $this->spin(function (SpecialContext $context) use ($page, $count, $elementClass) {
            try {
                $elements = $context->getMultipleElement($page, $elementClass);
                Helper::assertElementCount($elements, $count);
                return true;
            } catch (ResponseTextException $e) {
                // NOOP
            }
            return false;
        });
    }

    /**
     * Based on Behat's own example
     * @see http://docs.behat.org/en/v2.5/cookbook/using_spin_functions.html#adding-a-timeout
     * @param $lambda
     * @param int $wait
     * @return bool
     * @throws \Exception
     */
    protected function spin($lambda, $wait = 60)
    {
        if (!$this->spinWithNoException($lambda, $wait)) {
            throw new \Exception("Spin function timed out after {$wait} seconds");
        }
    }

    /**
     * Based on Behat's own example
     * @see http://docs.behat.org/en/v2.5/cookbook/using_spin_functions.html#adding-a-timeout
     * @param $lambda
     * @param int $wait
     * @return bool
     */
    protected function spinWithNoException($lambda, $wait = 60)
    {
        $time = time();
        $stopTime = $time + $wait;
        while (time() < $stopTime) {
            try {
                if ($lambda($this)) {
                    return true;
                }
            } catch (\Exception $e) {
                // do nothing
            }

            usleep(250000);
        }

        return false;
    }

    /**
     * @When /^I follow the link "(?P<linkName>[^"]*)" of the (page "[^"]*")$/
     */
    public function iFollowTheLinkOfThePage($linkName, Page $page)
    {
        Helper::clickNamedLink($page, $linkName);
    }

    /**
     * @When /^I follow the link of the element "(?P<elementClass>[^"]*)"$/
     * @When /^I follow the link of the element "(?P<elementClass>[^"]*)" on position (?P<position>\d+)$/
     */
    public function iFollowTheLinkOfTheElement($elementClass, $position = 1)
    {
        $this->iFollowTheLinkOfTheElementOnPosition(null, $elementClass, $position);
    }

    /**
     * @When /^I follow the link "(?P<linkName>[^"]*)" of the element "(?P<elementClass>[^"]*)"$/
     * @When /^I follow the link "(?P<linkName>[^"]*)" of the element "(?P<elementClass>[^"]*)" on position (?P<position>\d+)$/
     */
    public function iFollowTheLinkOfTheElementOnPosition($linkName, $elementClass, $position = 1)
    {
        /** @var HelperSelectorInterface $element */
        $element = $this->getElement($elementClass);

        if ($element instanceof MultipleElement) {
            /** @var Homepage $page */
            $page = $this->getPage('Homepage');

            /** @var MultipleElement $element */
            $element->setParent($page);

            $element = $element->setInstance($position);
        }

        if (empty($linkName)) {
            $this->clickElementWhenClickable($element);
            return;
        }

        $this->clickNamedLinkWhenClickable($element, $linkName);
    }

    /**
     * Tries to click on a named link until the click is successfull or the timeout is reached
     * @param HelperSelectorInterface $element
     * @param $linkName
     * @param int $timeout Defaults to 60 seconds
     */
    protected function clickNamedLinkWhenClickable(HelperSelectorInterface $element, $linkName, $timeout = 60)
    {
        $this->spin(function (SpecialContext $context) use ($element, $linkName) {
            try {
                Helper::clickNamedLink($element, $linkName);
                return true;
            } catch (DriverException $e) {
                // NOOP
            }
            return false;
        }, $timeout);
    }

    /**
     * Tries to click on an element until the click is successfull or the timeout is reached
     * @param NodeElement $element
     * @param int $timeout Defaults to 60 seconds
     */
    protected function clickElementWhenClickable(NodeElement $element, $timeout = 60)
    {
        $this->spin(function (SpecialContext $context) use ($element) {
            try {
                $element->click();
                return true;
            } catch (DriverException $e) {
                // NOOP
            }
            return false;
        }, $timeout);
    }

    /**
     * @Given /^the "(?P<field>[^"]*)" field should contain:$/
     */
    public function theFieldShouldContain($field, PyStringNode $string)
    {
        $assert = new WebAssert($this->getSession());
        $assert->fieldValueEquals($field, $string->getRaw());
    }

    /**
     * @When /^I press the button "([^"]*)" of the element "([^"]*)" on position (\d+)$/
     */
    public function iPressTheButtonOfTheElementOnPosition($linkName, $elementClass, $position = 1)
    {
        /** @var HelperSelectorInterface $element */
        $element = $this->getElement($elementClass);

        if ($element instanceof MultipleElement) {
            /** @var Homepage $page */
            $page = $this->getPage('Homepage');

            /** @var MultipleElement $element */
            $element->setParent($page);

            $element = $element->setInstance($position);
        }

        if (empty($linkName)) {
            $element->press();
            return;
        }

        Helper::pressNamedButton($element, $linkName);
    }
}
