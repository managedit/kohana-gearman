# About Gearman

Gearman provides a generic application framework to farm out work to other machines or processes that are better suited to do the work. It allows you to do work in parallel, to load balance processing, and to call functions between languages. It can be used in a variety of applications, from high-availability web sites to the transport of database replication events. In other words, it is the nervous system for how distributed processing communicates. A few strong points about Gearman:

* **Open Source** - It's free! (in both meanings of the word) Gearman has an active open source community that is easy to get involved with if you need help or want to contribute.
* **Multi-language** - There are interfaces for a number of languages, and this list is growing. You also have the option to write heterogeneous applications with clients submitting work in one language and workers performing that work in another.
* **Flexible** - You are not tied to any specific design pattern. You can quickly put together distributed applications using any model you choose, one of those options being Map/Reduce.
* **Fast** - Gearman has a simple protocol and interface with a new optimized server in C to minimize your application overhead.
* **Embeddable** - Since Gearman is fast and lightweight, it is great for applications of all sizes. It is also easy to introduce into existing applications with minimal overhead.
* **No single point of failure** - Gearman can not only help scale systems, but can do it in a fault tolerant way.

The first implementation of Gearman came from the folks at Danga Interactive (LiveJournal/SixApart). The name is an anagram for “Manager,” since it dispatches jobs to be done, but does not do anything useful itself. This wiki was setup to provide a single place to organize all information related to Gearman. Content is being updated regularly, so please check back often. You may also want to check out other forms of communication if you would like to learn more or get involved!
