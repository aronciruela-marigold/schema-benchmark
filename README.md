For benchmarking possible additional latency in our Mongo operations

Sample Output:
```
Pinged your deployment. You successfully connected to MongoDB!

Running Schemas Benchmark
Creating collection no_validation
Creating indexes no_validation

Creating collection with_validation
Creating indexes with_validation

Running CRUD benchmark with 1000 Documents in 5 Iterations
# ITERATION 1
## For collection no_validation
### Insert Exec Time: 0.62s
### Find Exec Time: 0.23s
### Update Exec Time: 1.04s
### Delete Exec Time: 1.03s
### Execution Time: 2.93s

## For collection with_validation
### Insert Exec Time: 0.53s
### Find Exec Time: 0.23s
### Update Exec Time: 1.07s
### Delete Exec Time: 1.03s
### Execution Time: 2.86s

# ITERATION 2
## For collection with_validation
### Insert Exec Time: 0.58s
### Find Exec Time: 0.26s
### Update Exec Time: 1.04s
### Delete Exec Time: 1.02s
### Execution Time: 2.89s

## For collection no_validation
### Insert Exec Time: 0.52s
### Find Exec Time: 0.25s
### Update Exec Time: 1.05s
### Delete Exec Time: 1.02s
### Execution Time: 2.84s

# ITERATION 3
## For collection no_validation
### Insert Exec Time: 0.59s
### Find Exec Time: 0.29s
### Update Exec Time: 1.09s
### Delete Exec Time: 1.03s
### Execution Time: 2.99s

## For collection with_validation
### Insert Exec Time: 0.59s
### Find Exec Time: 0.29s
### Update Exec Time: 1.11s
### Delete Exec Time: 1.04s
### Execution Time: 3.03s

# ITERATION 4
## For collection with_validation
### Insert Exec Time: 0.58s
### Find Exec Time: 0.32s
### Update Exec Time: 1.15s
### Delete Exec Time: 1.02s
### Execution Time: 3.07s

## For collection no_validation
### Insert Exec Time: 0.61s
### Find Exec Time: 0.32s
### Update Exec Time: 1.23s
### Delete Exec Time: 1.10s
### Execution Time: 3.26s

# ITERATION 5
## For collection no_validation
### Insert Exec Time: 0.57s
### Find Exec Time: 0.36s
### Update Exec Time: 1.38s
### Delete Exec Time: 1.10s
### Execution Time: 3.42s

## For collection with_validation
### Insert Exec Time: 0.59s
### Find Exec Time: 0.35s
### Update Exec Time: 1.28s
### Delete Exec Time: 1.07s
### Execution Time: 3.29s

# TOTAL in 5 ITERATIONS
## Collection with_validation
### Total execution time 15.13s
### Average 3.03s
### Standard Deviation 0.15s

## Collection no_validation
### Total execution time 15.44s
### Average 3.09s
### Standard Deviation 0.22s

```
