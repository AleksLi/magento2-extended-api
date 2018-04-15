## Extended Magento 2 API

### Description
I've added couple new features to make your life easier.
Please scroll down to see them.

### Installation
##### Using Composer (recommended)
```
composer require aleksli/module-extended-api
```
### Features
#### 1.Get Customer List
*you can get the list of all the customers*

##### Usage
__BASE_URL/rest/V1/customersList/__ 

#### 2. Get Stock Items List by SKUs
*you can get the list of stock items writing their skus*

##### Usage
__BASE_URL/rest//V1/stockItemsList/:productSKUs__ 
