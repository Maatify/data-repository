# Phase 5 Interface Matrix

| Method | Category | Implemented Today (Generic Classes) | In `RepositoryInterface`? | Used By MySQL | Used By Mongo | Used By Redis | Notes |
| :--- | :--- | :--- | :--- | :--- | :--- | :--- | :--- |
| `find(id)` | Read | ✅ Yes | ✅ Yes | ✅ Yes | ✅ Yes | ✅ Yes | Core read method. |
| `findBy(filters)` | Read | ✅ Yes | ✅ Yes | ✅ Yes | ✅ Yes | ✅ Yes | Core read method. |
| `findAll()` | Read | ✅ Yes | ✅ Yes | ✅ Yes | ✅ Yes | ✅ Yes | Core read method. |
| `findOneBy(filters)` | Read | ✅ Yes | ❌ No | ✅ Yes | ✅ Yes | ✅ Yes | Implemented in generic classes but missing from the interface contract. |
| `count(filters)` | Read | ✅ Yes | ❌ No | ✅ Yes | ✅ Yes | ⚠️ Partial | **Risk:** Redis implementation throws `RepositoryException` if filters are provided, violating ADR-003 constraints against runtime exceptions. |
| `paginate(...)` | Read | ✅ Yes | ❌ No | ✅ Yes | ✅ Yes | ✅ Yes | Implemented in generic classes but missing from the interface contract. |
| `paginateBy(...)` | Read | ✅ Yes | ❌ No | ✅ Yes | ✅ Yes | ✅ Yes | Implemented in generic classes but missing from the interface contract. |
| `insert(data)` | Write | ✅ Yes | ✅ Yes | ✅ Yes | ✅ Yes | ✅ Yes | Core write method. |
| `update(id, data)` | Write | ✅ Yes | ✅ Yes | ✅ Yes | ✅ Yes | ✅ Yes | Core write method. |
| `delete(id)` | Write | ✅ Yes | ✅ Yes | ✅ Yes | ✅ Yes | ✅ Yes | Core write method. |
| `setAdapter(adapter)` | Other | ✅ Yes | ✅ Yes | ✅ Yes | ✅ Yes | ✅ Yes | Configuration method. |
