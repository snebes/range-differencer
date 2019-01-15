## Range Differencer

[![PHP Version](https://img.shields.io/packagist/php-v/snebes/range-differencer.svg)](https://packagist.org/packages/snebes/range-differencer)
[![Latest Version](https://img.shields.io/packagist/v/snebes/range-differencer.svg)](https://packagist.org/packages/snebes/range-differencer)
[![Build Status](https://img.shields.io/scrutinizer/build/g/snebes/range-differencer.svg)](https://scrutinizer-ci.com/g/snebes/range-differencer)
[![Code Quality](https://img.shields.io/scrutinizer/g/snebes/range-differencer.svg)](https://scrutinizer-ci.com/g/snebes/range-differencer)
[![Test Coverage](https://img.shields.io/scrutinizer/coverage/g/snebes/range-differencer.svg)](https://scrutinizer-ci.com/g/snebes/range-differencer)

Provides support for finding the differences between two or three sequences of comparable entities.

### Specification

The class RangeDifferencer finds longest sequences of matching and non-matching comparable entities.
Clients must supply the input to the differencer as an implementation of the RangeComparatorInterface. An RangeComparatorInterface breaks the input data into a sequence of entities and provides a method for comparing one entity with the entity in another RangeComparatorInterface.

For example, to compare two text documents and find longest common sequences of matching and non-matching lines, the implementation of RangeComparatorInterface must break the document into lines and provide a method for testing whether two lines are considered equal. See TagComparator for how this can be done.

The differencer returns the differences among these sequences as an array of RangeDifference objects. Every single RangeDifference describes the kind of difference (no change, change, addition, deletion) and the corresponding ranges of the underlying comparable entities in the two or three inputs.
