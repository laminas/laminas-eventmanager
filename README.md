# laminas-eventmanager

[![Build Status](https://github.com/laminas/laminas-eventmanager/actions/workflows/continuous-integration.yml/badge.svg)](https://github.com/laminas/laminas-eventmanager/actions/workflows/continuous-integration.yml)
[![Coverage Status](https://coveralls.io/repos/laminas/laminas-eventmanager/badge.svg?branch=master)](https://coveralls.io/r/laminas/laminas-eventmanager?branch=master)

> ## 🇷🇺 Русским гражданам
>
> Мы, участники Laminas, родились и живем в разных странах. У многих из нас есть друзья, родственники и коллеги как в России, так и в Украине. Некоторые из нас родились в России. Некоторые из нас живут в России. У некоторых бабушки и дедушки сражались с фашистами во Второй мировой войне. Здесь никто не поддерживает фашизм.
>
> У одного из нас есть украинская родственница, которая спаслась из дома вместе с сыном. Поезд задержался из-за бомбежки на дороге впереди. У нас есть друзья, которые прячутся в бомбоубежищах. Мы с тревогой ждем весточки от них после воздушных налетов, которые беспорядочно наносят удары и попадают по больницам, школам, детским садам и домам. Мы не берем это из каких-либо СМИ. Мы наблюдаем это напрямую.
>
> Вы доверяете нам достаточно, чтоб использовать наши программы, и мы просим вас довериться нам вновь. Мы нуждаемся в помощи. Выходите и протестуйте против этой бесполезной войны. Остановите кровопролитие. Скажите "Нет войне!"
>
> ## 🇺🇸 To Citizens of Russia
>
> We at Laminas come from all over the world. Many of us have friends, family and colleagues in both Russia and Ukraine. Some of us were born in Russia. Some of us currently live in Russia. Some have grandparents who fought Nazis in World War II. Nobody here supports fascism.
>
> One team member has a Ukrainian relative who fled her home with her son. The train was delayed due to bombing on the road ahead. We have friends who are hiding in bomb shelters. We anxiously follow up on them after the air raids, which indiscriminately fire at hospitals, schools, kindergartens and houses. We're not taking this from any media. These are our actual experiences.
>
> You trust us enough to use our software. We ask that you trust us to say the truth on this. We need your help. Go out and protest this unnecessary war. Stop the bloodshed. Say "stop the war!"

laminas-eventmanager is designed for the following use cases:

- Implementing simple subject/observer patterns.
- Implementing Aspect-Oriented designs.
- Implementing event-driven architectures.

The basic architecture allows you to attach and detach listeners to named events,
both on a per-instance basis as well as via shared collections; trigger events;
and interrupt execution of listeners.

- File issues at [https://github.com/laminas/laminas-eventmanager/issues](https://github.com/laminas/laminas-eventmanager/issues)
- Documentation is at [https://docs.laminas.dev/laminas-eventmanager/](https://docs.laminas.dev/laminas-eventmanager/)

For migration from version 2 to version 3, please [read the migration
documentation](https://docs.laminas.dev/laminas-eventmanager/migration/intro/).

## Benchmarks

We provide scripts for benchmarking laminas-eventmanager using the
[PHPBench](https://github.com/phpbench/phpbench) framework; these can be
found in the `benchmarks/` directory.

To execute the benchmarks you can run the following command:

```bash
$ vendor/bin/phpbench run --report=aggregate
```
