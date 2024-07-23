# Upgrade Guide

## 1.x to 2.x

2.x introduces a complete refactor of the package structure.

A few highlights:
- Simplified implementation
- Support for in backorder, notify stock
- Support updating stock via Magento 2 bulk async requests

### Update your project

The stock retriever, SKU retriever and calculator classes all have been merged into a single repository class.
Refer to the readme on how to implement this.

The configuration file has been stripped, most of the configuration is now done in the repository class.


A lot of classes have been renamed, be sure to update your scheduler and check all classes that you use.
The stock model has been renamed from `MagentoStock` to `Stock`.


