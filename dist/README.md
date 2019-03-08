## Files

* `SuperSQL.php` - Main file
* `SuperSQL_min.php`
* `SuperSQL_helper.php` - Helper functions
* `SuperSQL_helper_min.php`

### Sizes

* `SuperSQL.php` - 28250 Chars (28.3 MB)
* `SuperSQL_min.php` - 12501 Chars (12.5 MB)
* `SuperSQL_helper.php` - 11234 Chars (11.2 MB)
* `SuperSQL_helper_min.php` - 5558 Chars (5.6 MB)
* `SuperSQL_complete_min.php` - 17779 Chars (17.8 MB)

## Hashes

```
* SuperSQL.php - 6b8a0ca5b29cc2941e243eedfaaf6a43
* SuperSQL_min.php - f8e4fa363345276e1a908322ef10b969
* SuperSQL_helper.php - e89949fcc3b648c678b4ff55c14ec4c0
* SuperSQL_helper_min.php - 8376bf405228152b1b37a5e659ab3287
* SuperSQL_complete.php - c50b9ae64111ba60684ac7e98b7d686e
```

## Performance

Profiled on PHP v7.1.4, 30 loops


0.0472ms Average Time, Sum: 1.4169ms

### Specifics

| Name                    |  Avg   |  Sum   |
|-------------------------|--------|--------|
| 1 Row Insert            | 0.003 | 0.091 |
| 100 R Insert W Temp     | 0.0347 | 1.0397 |
| Select *                | 0.0014 | 0.0408 |
| Select * W Cast         | 0.0012 | 0.0363 |
| Select * W Cast W where | 0.0014 | 0.041 |
| 1 Row Update            | 0.0031 | 0.0919 |
| Delete                  | 0.0024 | 0.071 |
